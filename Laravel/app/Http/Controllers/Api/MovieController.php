<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MovieController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Movie::all());
    }

    public function store(Request $request): JsonResponse
    {
        $movie = Movie::create($request->validate($this->rules()));

        return response()->json($movie, 201);
    }

    public function show(Movie $movie): JsonResponse
    {
        return response()->json($movie);
    }

    public function update(Request $request, Movie $movie): JsonResponse
    {
        // PATCH updates only the fields that were sent, PUT requires the full object
        $rules = $this->rules();
        if ($request->isMethod('patch')) {
            $rules = array_map(fn (array $r) => ['sometimes', ...$r], $rules);
        }

        $movie->update($request->validate($rules));

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
            'age_rating' => ['nullable', Rule::in(['0+', '6+', '12+', '16+', '18+'])],
        ];
    }
}
