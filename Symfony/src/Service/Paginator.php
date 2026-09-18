<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Page-based pagination: ?page=1&itemsPerPage=10.
 */
final class Paginator
{
    public const DEFAULT_ITEMS_PER_PAGE = 10;
    public const MAX_ITEMS_PER_PAGE = 100;

    /**
     * @param array<string, mixed> $query request query parameters
     *
     * @return array{data: list<object>, meta: array{page: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    public function paginate(QueryBuilder $qb, array $query): array
    {
        $page = $this->positiveInt($query, 'page', 1);
        $itemsPerPage = $this->positiveInt($query, 'itemsPerPage', self::DEFAULT_ITEMS_PER_PAGE);
        if ($itemsPerPage > self::MAX_ITEMS_PER_PAGE) {
            throw new BadRequestHttpException(sprintf('Parameter "itemsPerPage" must not exceed %d.', self::MAX_ITEMS_PER_PAGE));
        }

        $qb->setFirstResult(($page - 1) * $itemsPerPage)->setMaxResults($itemsPerPage);
        $paginator = new DoctrinePaginator($qb, fetchJoinCollection: false);
        $totalItems = count($paginator);

        return [
            'data' => iterator_to_array($paginator, false),
            'meta' => [
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalItems,
                'totalPages' => (int) ceil($totalItems / $itemsPerPage),
            ],
        ];
    }

    private function positiveInt(array $query, string $name, int $default): int
    {
        $value = $query[$name] ?? null;
        if ($value === null || $value === '') {
            return $default;
        }
        if (!is_string($value) || !ctype_digit($value) || (int) $value < 1) {
            throw new BadRequestHttpException(sprintf('Parameter "%s" must be a positive integer.', $name));
        }

        return (int) $value;
    }
}
