<?php

namespace App\Service;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Applies filters and sorting from query-string parameters to a Doctrine QueryBuilder.
 *
 * Filter definitions: ['param' => type] or ['param' => [type, 'entityField']].
 *   string              ?title=dune              — case-insensitive "contains"
 *   int|decimal         ?price=250               — exact, or range ?price[gte]=100&price[lte]=300
 *   date                ?releaseDate=2024-03-01  — exact (Y-m-d), or range with gte/lte/gt/lt
 *   datetime            ?startsAt=2026-09-20     — whole day, or exact "Y-m-d H:i", or range
 *   enum                ?status=paid             — exact
 *   relation            ?movieId=3               — related entity id
 * Sorting: ?sort=<param>&order=asc|desc (relations are not sortable).
 */
final class QueryFilter
{
    public const STRING = 'string';
    public const INT = 'int';
    public const DECIMAL = 'decimal';
    public const DATE = 'date';
    public const DATETIME = 'datetime';
    public const ENUM = 'enum';
    public const RELATION = 'relation';

    private const RANGE_OPERATORS = ['gte' => '>=', 'lte' => '<=', 'gt' => '>', 'lt' => '<'];
    private const RANGE_TYPES = [self::INT, self::DECIMAL, self::DATE, self::DATETIME];

    private int $parameterIndex = 0;

    /**
     * @param array<string, string|array{string, string}> $definitions
     * @param array<string, mixed>                        $query       request query parameters
     */
    public function apply(QueryBuilder $qb, array $definitions, array $query): void
    {
        $alias = $qb->getRootAliases()[0];

        foreach ($definitions as $param => $definition) {
            if (!array_key_exists($param, $query) || $query[$param] === '' || $query[$param] === []) {
                continue;
            }
            [$type, $field] = is_array($definition) ? $definition : [$definition, $param];
            $this->applyFilter($qb, "$alias.$field", $type, $param, $query[$param]);
        }

        $this->applySorting($qb, $alias, $definitions, $query);
    }

    private function applyFilter(QueryBuilder $qb, string $column, string $type, string $param, mixed $value): void
    {
        if (is_array($value)) {
            if (!in_array($type, self::RANGE_TYPES, true)) {
                throw new BadRequestHttpException(sprintf('Filter "%s" does not support ranges.', $param));
            }
            foreach ($value as $operator => $bound) {
                if (!isset(self::RANGE_OPERATORS[$operator])) {
                    throw new BadRequestHttpException(sprintf('Unknown operator "%s" for filter "%s", use gte, lte, gt or lt.', $operator, $param));
                }
                $this->where($qb, $column, self::RANGE_OPERATORS[$operator], $this->cast($type, $param, $bound), $type);
            }

            return;
        }

        if ($type === self::STRING) {
            $p = $this->nextParameter();
            $qb->andWhere("LOWER($column) LIKE :$p")
                ->setParameter($p, '%'.addcslashes(mb_strtolower((string) $value), '%_').'%');

            return;
        }

        // A plain date for a datetime column means "any time on that day"
        if ($type === self::DATETIME && is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $day = $this->cast(self::DATETIME, $param, $value);
            $this->where($qb, $column, '>=', $day, $type);
            $this->where($qb, $column, '<', $day->modify('+1 day'), $type);

            return;
        }

        $this->where($qb, $column, '=', $this->cast($type, $param, $value), $type);
    }

    private function where(QueryBuilder $qb, string $column, string $operator, mixed $value, string $type): void
    {
        $p = $this->nextParameter();
        $qb->andWhere("$column $operator :$p")->setParameter($p, $value, match ($type) {
            self::DATE => Types::DATE_IMMUTABLE,
            self::DATETIME => Types::DATETIME_IMMUTABLE,
            default => null,
        });
    }

    private function cast(string $type, string $param, mixed $value): mixed
    {
        if (!is_scalar($value)) {
            throw new BadRequestHttpException(sprintf('Invalid value for filter "%s".', $param));
        }
        $value = (string) $value;

        $result = match ($type) {
            self::INT, self::RELATION => ctype_digit($value) ? (int) $value : null,
            self::DECIMAL => is_numeric($value) ? $value : null,
            self::DATE => $this->parseDate('!Y-m-d', $value),
            self::DATETIME => $this->parseDate('!Y-m-d H:i', $value) ?? $this->parseDate('!Y-m-d', $value),
            default => $value,
        };

        if ($result === null) {
            $expected = match ($type) {
                self::INT, self::RELATION => 'an integer',
                self::DECIMAL => 'a number',
                self::DATE => 'a date in Y-m-d format',
                self::DATETIME => 'a date in Y-m-d or Y-m-d H:i format',
            };
            throw new BadRequestHttpException(sprintf('Filter "%s" must be %s.', $param, $expected));
        }

        return $result;
    }

    private function parseDate(string $format, string $value): ?\DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat($format, $value);

        return $date !== false && $date->format(substr($format, 1)) === $value ? $date : null;
    }

    private function applySorting(QueryBuilder $qb, string $alias, array $definitions, array $query): void
    {
        $sort = $query['sort'] ?? 'id';
        $order = strtolower((string) ($query['order'] ?? 'asc'));

        if (!is_string($sort) || !isset($definitions[$sort])) {
            throw new BadRequestHttpException(sprintf('Cannot sort by "%s".', is_string($sort) ? $sort : 'array'));
        }
        [$type, $field] = is_array($definitions[$sort]) ? $definitions[$sort] : [$definitions[$sort], $sort];
        if ($type === self::RELATION) {
            throw new BadRequestHttpException(sprintf('Cannot sort by "%s".', $sort));
        }
        if (!in_array($order, ['asc', 'desc'], true)) {
            throw new BadRequestHttpException('Parameter "order" must be "asc" or "desc".');
        }

        $qb->orderBy("$alias.$field", $order);
        if ($field !== 'id') {
            $qb->addOrderBy("$alias.id", 'asc');
        }
    }

    private function nextParameter(): string
    {
        return 'filter_'.$this->parameterIndex++;
    }
}
