<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfiguracionController;
use Illuminate\Support\Facades\Route;

// Authentication endpoints
Route::prefix('auth')->group(function (): void {
    Route::middleware('guest')->post('/login', [AuthController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AuthController::class, 'show']);
        Route::post('/logout', [AuthController::class, 'destroy']);
    });
});

// Protected application endpoints
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/configuraciones/publicas', [ConfiguracionController::class, 'publicas']);

    Route::middleware('permission:dashboard.view')->get('/dashboard-data', function () {
        return response()->json([
            'message' => 'Dashboard API is ready.',
            'timestamp' => now()->toIso8601String(),
        ]);
    });
});

// SPA entry point (exclude backend endpoints)
Route::view('/{path?}', 'app')
    ->where('path', '^(?!auth|configuraciones|dashboard-data|sanctum).*$');
