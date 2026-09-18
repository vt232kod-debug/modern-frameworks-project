<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class TestController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'framework' => 'Laravel ' . app()->version(),
            'message' => 'Test method works',
        ]);
    }
}
