<?php

namespace App\Http\Controllers\Api;

use App\Models\Hall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HallController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->listResponse($request, Hall::query(), Hall::FILTERS, fn (Hall $hall) => $hall->append('capacity'));
    }

    public function store(Request $request): JsonResponse
    {
        $hall = Hall::create($this->validatePayload($request, $this->rules()));

        return response()->json($hall->append('capacity'), 201);
    }

    public function show(Hall $hall): JsonResponse
    {
        return response()->json($hall->append('capacity'));
    }

    public function update(Request $request, Hall $hall): JsonResponse
    {
        $hall->update($this->validatePayload($request, $this->rules($hall)));

        return response()->json($hall->append('capacity'));
    }

    public function destroy(Hall $hall): JsonResponse
    {
        $hall->delete();

        return response()->json(null, 204);
    }

    private function rules(?Hall $hall = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('halls')->ignore($hall)],
            'type' => ['required', Rule::in(Hall::TYPES)],
            'rows_count' => ['required', 'integer', 'between:1,50'],
            'seats_per_row' => ['required', 'integer', 'between:1,50'],
        ];
    }
}
