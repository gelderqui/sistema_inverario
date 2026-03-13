<?php

// Las rutas de autenticación y de la SPA están en routes/web.php.
// Este archivo queda reservado para futuras integraciones de API externa o móvil.
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => response()->json(['status' => 'ok']));