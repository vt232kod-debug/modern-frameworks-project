<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\HallController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\ScreeningController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::apiResource('movies', MovieController::class);
Route::apiResource('halls', HallController::class);
Route::apiResource('screenings', ScreeningController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('tickets', TicketController::class);
