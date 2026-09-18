<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
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
