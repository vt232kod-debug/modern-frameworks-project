<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

/**
 * Page-based pagination: ?page=1&itemsPerPage=10.
 */
class Paginator
{
    public const DEFAULT_ITEMS_PER_PAGE = 10;
    public const MAX_ITEMS_PER_PAGE = 100;

    /**
     * @return array{data: array, meta: array{page: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    public function paginate(Builder $builder, array $query): array
    {
        $page = $this->positiveInt($query, 'page', 1);
        $itemsPerPage = $this->positiveInt($query, 'itemsPerPage', self::DEFAULT_ITEMS_PER_PAGE);
        if ($itemsPerPage > self::MAX_ITEMS_PER_PAGE) {
            abort(400, 'Parameter "itemsPerPage" must not exceed '.self::MAX_ITEMS_PER_PAGE.'.');
        }

        $paginator = $builder->paginate($itemsPerPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $paginator->total(),
                'totalPages' => (int) ceil($paginator->total() / $itemsPerPage),
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
            abort(400, "Parameter \"$name\" must be a positive integer.");
        }

        return (int) $value;
    }
}
