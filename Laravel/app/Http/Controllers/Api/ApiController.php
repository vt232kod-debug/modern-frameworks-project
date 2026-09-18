<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Paginator;
use App\Services\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    public function __construct(
        protected readonly QueryFilter $queryFilter,
        protected readonly Paginator $paginator,
    ) {
    }

    /**
     * Filtered, sorted and paginated list: {data: [...], meta: {page, itemsPerPage, totalItems, totalPages}}.
     */
    protected function listResponse(Request $request, Builder $builder, array $filters, ?callable $transform = null): JsonResponse
    {
        $query = $request->query();
        $this->queryFilter->apply($builder, $filters, $query);
        $result = $this->paginator->paginate($builder, $query);
        if ($transform !== null) {
            $result['data'] = array_map($transform, $result['data']);
        }

        return response()->json($result);
    }

    /**
     * Validates the request body. PATCH updates only the fields that were sent,
     * POST/PUT require the full object.
     */
    protected function validatePayload(Request $request, array $rules): array
    {
        if ($request->isMethod('patch')) {
            $rules = array_map(fn (array $r) => ['sometimes', ...$r], $rules);
        }

        return $request->validate($rules);
    }
}
