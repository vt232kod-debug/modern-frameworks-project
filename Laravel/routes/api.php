<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\HallController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\ScreeningController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
| Access (admin > manager > client, a higher role has all rights of the lower ones):
|   movies, halls, screenings — read: client; create/update: manager; delete: admin
|   customers                  — read/create/update: manager; delete: admin
|   tickets                    — client: own tickets only (list, view, buy); update: manager; delete: admin
|   users                      — admin only
*/

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);

    foreach (['movies' => MovieController::class, 'halls' => HallController::class, 'screenings' => ScreeningController::class] as $name => $controller) {
        Route::apiResource($name, $controller)->only(['index', 'show']);
        Route::apiResource($name, $controller)->only(['store', 'update'])->middleware('role:manager');
        Route::apiResource($name, $controller)->only(['destroy'])->middleware('role:admin');
    }

    Route::apiResource('customers', CustomerController::class)->except(['destroy'])->middleware('role:manager');
    Route::apiResource('customers', CustomerController::class)->only(['destroy'])->middleware('role:admin');

    // Ownership of tickets for clients is checked in TicketController
    Route::apiResource('tickets', TicketController::class)->only(['index', 'show', 'store']);
    Route::apiResource('tickets', TicketController::class)->only(['update'])->middleware('role:manager');
    Route::apiResource('tickets', TicketController::class)->only(['destroy'])->middleware('role:admin');

    Route::apiResource('users', UserController::class)->middleware('role:admin');
});
