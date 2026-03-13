<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfiguracionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::middleware('guest')->post('/login', [AuthController::class, 'store']);

    Route::middleware('auth')->group(function (): void {
        Route::get('/me', [AuthController::class, 'show']);
        Route::post('/logout', [AuthController::class, 'destroy']);
    });
});

Route::get('/configuraciones/publicas', [ConfiguracionController::class, 'publicas']);

Route::middleware(['auth', 'permission:dashboard.view'])->get('/dashboard-data', function () {
    return response()->json([
        'message' => 'Dashboard API is ready.',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::view('/{path?}', 'app')
    ->where('path', '^(?!auth|configuraciones|dashboard-data|sanctum).*$');
