<?php

namespace App\Http\Controllers\Api;

use App\Models\Screening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScreeningController extends ApiController
{
    private const RELATIONS = ['movie:id,title', 'hall:id,name,type'];

    public function index(): JsonResponse
    {
        return response()->json(Screening::with(self::RELATIONS)->get());
    }

    public function store(Request $request): JsonResponse
    {
        $screening = Screening::create($this->validatePayload($request, $this->rules()));

        return response()->json($screening->load(self::RELATIONS), 201);
    }

    public function show(Screening $screening): JsonResponse
    {
        return response()->json($screening->load(self::RELATIONS));
    }

    public function update(Request $request, Screening $screening): JsonResponse
    {
        $screening->update($this->validatePayload($request, $this->rules()));

        return response()->json($screening->load(self::RELATIONS));
    }

    public function destroy(Screening $screening): JsonResponse
    {
        $screening->delete();

        return response()->json(null, 204);
    }

    private function rules(): array
    {
        return [
            'movie_id' => ['required', 'integer', 'exists:movies,id'],
            'hall_id' => ['required', 'integer', 'exists:halls,id'],
            'starts_at' => ['required', 'date_format:Y-m-d H:i'],
            'price' => ['required', 'numeric', 'min:0'],
            'language' => ['required', 'string', 'max:20'],
        ];
    }
}
