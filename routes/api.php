<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

// Rutas públicas de autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);

    // Rutas protegidas por el Middleware
    Route::get('/orders/{id}', [OrderController::class, 'show'])->middleware('order.owner');
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel'])->middleware('order.owner');
});