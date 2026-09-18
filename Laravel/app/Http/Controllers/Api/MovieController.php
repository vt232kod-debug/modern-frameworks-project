<?php

namespace App\Http\Controllers\Api;

use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MovieController extends ApiController
{
    public function index(): JsonResponse
    {
        return response()->json(Movie::all());
    }

    public function store(Request $request): JsonResponse
    {
        $movie = Movie::create($this->validatePayload($request, $this->rules()));

        return response()->json($movie, 201);
    }

    public function show(Movie $movie): JsonResponse
    {
        return response()->json($movie);
    }

    public function update(Request $request, Movie $movie): JsonResponse
    {
        $movie->update($this->validatePayload($request, $this->rules()));

        return response()->json($movie);
    }

    public function destroy(Movie $movie): JsonResponse
    {
        $movie->delete();

        return response()->json(null, 204);
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'genre' => ['required', 'string', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'release_date' => ['nullable', 'date_format:Y-m-d'],
            'age_rating' => ['nullable', Rule::in(Movie::AGE_RATINGS)],
        ];
    }
}
