<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Applies filters and sorting from query-string parameters to an Eloquent query.
 *
 * Filter definitions: ['column' => type].
 *   string              ?title=dune              — case-insensitive "contains"
 *   int|decimal         ?price=250               — exact, or range ?price[gte]=100&price[lte]=300
 *   date                ?release_date=2024-03-01 — exact (Y-m-d), or range with gte/lte/gt/lt
 *   datetime            ?starts_at=2026-09-20    — whole day, or exact "Y-m-d H:i", or range
 *   enum                ?status=paid             — exact
 *   relation            ?movie_id=3              — related record id
 * Sorting: ?sort=<column>&order=asc|desc.
 */
class QueryFilter
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

    /**
     * @param array<string, string> $definitions column => type
     * @param array<string, mixed>  $query       request query parameters
     */
    public function apply(Builder $builder, array $definitions, array $query): Builder
    {
        foreach ($definitions as $column => $type) {
            $value = $query[$column] ?? null;
            if ($value === null || $value === '' || $value === []) {
                continue;
            }
            $this->applyFilter($builder, $builder->qualifyColumn($column), $type, $column, $value);
        }

        return $this->applySorting($builder, $definitions, $query);
    }

    private function applyFilter(Builder $builder, string $column, string $type, string $param, mixed $value): void
    {
        if (is_array($value)) {
            if (!in_array($type, self::RANGE_TYPES, true)) {
                abort(400, "Filter \"$param\" does not support ranges.");
            }
            foreach ($value as $operator => $bound) {
                if (!isset(self::RANGE_OPERATORS[$operator])) {
                    abort(400, "Unknown operator \"$operator\" for filter \"$param\", use gte, lte, gt or lt.");
                }
                $builder->where($column, self::RANGE_OPERATORS[$operator], $this->cast($type, $param, $bound));
            }

            return;
        }

        if ($type === self::STRING) {
            $builder->whereRaw("LOWER($column) LIKE ?", ['%'.addcslashes(mb_strtolower((string) $value), '%_\\').'%']);

            return;
        }

        // A plain date for a datetime column means "any time on that day"
        if ($type === self::DATETIME && is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $day = $this->cast(self::DATETIME, $param, $value);
            $builder->where($column, '>=', $day)->where($column, '<', $day->addDay());

            return;
        }

        $builder->where($column, $this->cast($type, $param, $value));
    }

    private function cast(string $type, string $param, mixed $value): mixed
    {
        if (!is_scalar($value)) {
            abort(400, "Invalid value for filter \"$param\".");
        }
        $value = (string) $value;

        $result = match ($type) {
            self::INT, self::RELATION => ctype_digit($value) ? (int) $value : null,
            self::DECIMAL => is_numeric($value) ? $value : null,
            self::DATE => $this->parseDate('Y-m-d', $value)?->toDateString(),
            self::DATETIME => $this->parseDate('Y-m-d H:i', $value) ?? $this->parseDate('Y-m-d', $value),
            default => $value,
        };

        if ($result === null) {
            $expected = match ($type) {
                self::INT, self::RELATION => 'an integer',
                self::DECIMAL => 'a number',
                self::DATE => 'a date in Y-m-d format',
                self::DATETIME => 'a date in Y-m-d or Y-m-d H:i format',
            };
            abort(400, "Filter \"$param\" must be $expected.");
        }

        return $result;
    }

    private function parseDate(string $format, string $value): ?CarbonImmutable
    {
        try {
            $date = CarbonImmutable::createFromFormat('!'.$format, $value);
        } catch (\Carbon\Exceptions\InvalidFormatException) {
            return null;
        }

        return $date !== null && $date->format($format) === $value ? $date : null;
    }

    private function applySorting(Builder $builder, array $definitions, array $query): Builder
    {
        $sort = $query['sort'] ?? 'id';
        $order = strtolower((string) ($query['order'] ?? 'asc'));

        if (!is_string($sort) || !isset($definitions[$sort])) {
            abort(400, 'Cannot sort by "'.(is_string($sort) ? $sort : 'array').'".');
        }
        if (!in_array($order, ['asc', 'desc'], true)) {
            abort(400, 'Parameter "order" must be "asc" or "desc".');
        }

        $builder->orderBy($builder->qualifyColumn($sort), $order);
        if ($sort !== 'id') {
            $builder->orderBy($builder->qualifyColumn('id'));
        }

        return $builder;
    }
}
