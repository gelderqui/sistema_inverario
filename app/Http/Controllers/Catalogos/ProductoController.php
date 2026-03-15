<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\UnidadMedida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    public function index(): JsonResponse
    {
        $productos = Producto::query()
            ->with(['categoria:id,nombre', 'proveedor:id,nombre', 'unidadMedida:id,nombre,abreviatura'])
            ->orderBy('nombre')
            ->get([
                'id',
                'categoria_id',
                'proveedor_id',
                'nombre',
                'codigo_barra',
                'detalle',
                'palabras_clave',
                'precio_venta',
                'costo_promedio',
                'stock_actual',
                'stock_minimo',
                'unidad_medida_id',
                'control_vencimiento',
                'dias_alerta_vencimiento',
                'peso_referencial',
                'activo',
                'created_at',
            ]);

        return response()->json([
            'data' => $productos,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'categoria_id'  => ['nullable', Rule::exists('categorias', 'id')],
            'proveedor_id'  => ['nullable', Rule::exists('proveedores', 'id')],
            'nombre'        => ['required', 'string', 'max:255'],
            'codigo_barra'  => ['nullable', 'string', 'max:100', 'unique:productos,codigo_barra'],
            'detalle'       => ['nullable', 'string'],
            'palabras_clave'=> ['nullable', 'string', 'max:500'],
            'precio_venta'  => ['nullable', 'numeric', 'gte:0'],
            'costo_promedio'=> ['nullable', 'numeric', 'gte:0'],
            'stock_actual'  => ['nullable', 'numeric'],
            'stock_minimo'  => ['nullable', 'numeric', 'gte:0'],
            'unidad_medida_id'  => ['required', Rule::exists('unidades_medida', 'id')->where(fn ($q) => $q->where('activo', true))],
            'control_vencimiento' => ['sometimes', 'boolean'],
            'dias_alerta_vencimiento' => ['nullable', 'integer', 'min:1', 'max:365'],
            'dias_alerta_vencimiento' => ['nullable', 'integer', 'min:1', 'max:365'],
            'peso_referencial' => ['nullable', 'numeric', 'gte:0'],
            'activo'        => ['sometimes', 'boolean'],
        ]);

        $producto = Producto::query()->create([
            ...$validated,
            'precio_venta'    => $validated['precio_venta'] ?? 0,
            'costo_promedio'  => $validated['costo_promedio'] ?? 0,
            'stock_actual'    => $validated['stock_actual'] ?? 0,
            'stock_minimo'    => $validated['stock_minimo'] ?? 0,
            'control_vencimiento' => (bool) ($validated['control_vencimiento'] ?? false),
            'dias_alerta_vencimiento' => $validated['dias_alerta_vencimiento'] ?? 15,
            'activo'          => (bool) ($validated['activo'] ?? true),
            'add_user'        => $request->user()->id,
        ]);

        $producto->load(['categoria:id,nombre', 'proveedor:id,nombre', 'unidadMedida:id,nombre,abreviatura']);

        return response()->json([
            'message' => 'Producto creado correctamente.',
            'data'    => $producto,
        ], 201);
    }

    public function update(Request $request, Producto $producto): JsonResponse
    {
        $validated = $request->validate([
            'categoria_id'  => ['nullable', Rule::exists('categorias', 'id')],
            'proveedor_id'  => ['nullable', Rule::exists('proveedores', 'id')],
            'nombre'        => ['required', 'string', 'max:255'],
            'codigo_barra'  => ['nullable', 'string', 'max:100', Rule::unique('productos', 'codigo_barra')->ignore($producto->id)],
            'detalle'       => ['nullable', 'string'],
            'palabras_clave'=> ['nullable', 'string', 'max:500'],
            'precio_venta'  => ['nullable', 'numeric', 'gte:0'],
            'costo_promedio'=> ['nullable', 'numeric', 'gte:0'],
            'stock_actual'  => ['nullable', 'numeric'],
            'stock_minimo'  => ['nullable', 'numeric', 'gte:0'],
            'unidad_medida_id'  => ['required', Rule::exists('unidades_medida', 'id')->where(fn ($q) => $q->where('activo', true))],
            'control_vencimiento' => ['sometimes', 'boolean'],
            'peso_referencial' => ['nullable', 'numeric', 'gte:0'],
            'activo'        => ['required', 'boolean'],
        ]);

        $producto->update([
            ...$validated,
            'precio_venta'    => $validated['precio_venta'] ?? $producto->precio_venta,
            'costo_promedio'  => $validated['costo_promedio'] ?? $producto->costo_promedio,
            'stock_actual'    => $validated['stock_actual'] ?? $producto->stock_actual,
            'stock_minimo'    => $validated['stock_minimo'] ?? $producto->stock_minimo,
            'control_vencimiento' => (bool) ($validated['control_vencimiento'] ?? $producto->control_vencimiento),
            'dias_alerta_vencimiento' => $validated['dias_alerta_vencimiento'] ?? $producto->dias_alerta_vencimiento,
            'activo'          => (bool) $validated['activo'],
            'mod_user'        => $request->user()->id,
        ]);

        $producto->load(['categoria:id,nombre', 'proveedor:id,nombre', 'unidadMedida:id,nombre,abreviatura']);

        return response()->json([
            'message' => 'Producto actualizado correctamente.',
            'data'    => $producto,
        ]);
    }

    public function toggle(Request $request, Producto $producto): JsonResponse
    {
        $producto->update([
            'activo'   => ! $producto->activo,
            'mod_user' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'data'    => $producto,
        ]);
    }

    public function destroy(Producto $producto): JsonResponse
    {
        if ($producto->compraDetalles()->exists() || $producto->movimientosInventario()->exists()) {
            return response()->json([
                'message' => 'No puede eliminar este producto porque tiene historial de compras o inventario.',
            ], 422);
        }

        $producto->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente.',
        ]);
    }
}
