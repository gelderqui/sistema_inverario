<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\UnidadMedida;
use Illuminate\Http\JsonResponse;

class MedidaController extends Controller
{
    public function index(): JsonResponse
    {
        $medidas = UnidadMedida::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'abreviatura']);

        return response()->json([
            'data' => $medidas,
        ]);
    }
}
