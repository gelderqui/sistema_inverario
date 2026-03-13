<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::middleware('guest')->post('/login', [AuthController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AuthController::class, 'show']);
        Route::post('/logout', [AuthController::class, 'destroy']);
    });
});

Route::middleware(['auth:sanctum', 'permission:dashboard.view'])->get('/dashboard', function () {
    return response()->json([
        'message' => 'Dashboard API is ready.',
        'timestamp' => now()->toIso8601String(),
    ]);
});