<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\JsonResponse;

class ConfiguracionController extends Controller
{
    public function publicas(): JsonResponse
    {
        return response()->json([
            'nombre_empresa' => Configuracion::valor('nombre_empresa', config('app.name', 'Sistema POS e Inventario')),
            'tiempo_sesion' => (int) Configuracion::valor('tiempo_sesion', 120),
            'locale' => Configuracion::valor('locale', config('app.locale', 'es')),
        ]);
    }
}
