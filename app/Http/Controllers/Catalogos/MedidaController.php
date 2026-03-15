<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Medida;
use Illuminate\Http\JsonResponse;

class MedidaController extends Controller
{
    public function index(): JsonResponse
    {
        $medidas = Medida::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre']);

        return response()->json([
            'data' => $medidas,
        ]);
    }
}
