<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\DetalleDevolucion;
use App\Models\Devolucion;
use App\Models\InventarioLote;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DevolucionController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Devolucion::query()
            ->with([
                'venta:id,numero,fecha_venta',
                'usuario:id,name',
                'detalles.producto:id,nombre',
            ])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get(['id', 'venta_id', 'usuario_id', 'fecha', 'total', 'created_at']);

        return response()->json([
            'data' => $items,
        ]);
    }

    public function catalogs(): JsonResponse
    {
        $ventas = Venta::query()
            ->with([
                'cliente:id,nombre',
                'detalles:id,venta_id,producto_id,cantidad,precio_unitario,subtotal',
                'detalles.producto:id,nombre,stock_actual,costo_promedio,control_vencimiento',
            ])
            ->orderByDesc('fecha_venta')
            ->orderByDesc('id')
            ->limit(120)
            ->get(['id', 'numero', 'cliente_id', 'fecha_venta', 'total']);

        $ventas->each(function (Venta $venta): void {
            $detalleIds = $venta->detalles->pluck('id');
            $devueltoByDetalle = DetalleDevolucion::query()
                ->select('venta_detalle_id', DB::raw('SUM(cantidad) as total_devuelto'))
                ->whereIn('venta_detalle_id', $detalleIds)
                ->groupBy('venta_detalle_id')
                ->pluck('total_devuelto', 'venta_detalle_id');

            $venta->detalles->transform(function ($detalle) use ($devueltoByDetalle) {
                $devuelto = (float) ($devueltoByDetalle[$detalle->id] ?? 0);
                $detalle->setAttribute('cantidad_devuelta', toMoney($devuelto, 4));
                $detalle->setAttribute('cantidad_disponible_devolucion', toMoney((float) $detalle->cantidad - $devuelto, 4));
                return $detalle;
            });
        });

        return response()->json([
            'data' => [
                'ventas' => $ventas,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'venta_id' => ['required', Rule::exists('ventas', 'id')],
            'fecha' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.venta_detalle_id' => ['required', Rule::exists('venta_detalles', 'id')],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'items.*.motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $userId = (int) $request->user()->id;

        $devolucion = DB::transaction(function () use ($validated, $userId) {
            $venta = Venta::query()
                ->with('detalles.producto:id,nombre,stock_actual,costo_promedio,control_vencimiento')
                ->lockForUpdate()
                ->findOrFail($validated['venta_id']);

            $detallesVenta = $venta->detalles->keyBy('id');

            $devolucion = Devolucion::query()->create([
                'venta_id' => $venta->id,
                'usuario_id' => $userId,
                'fecha' => $validated['fecha'],
                'total' => 0,
            ]);

            $total = 0.0;

            foreach ($validated['items'] as $item) {
                $detalleVenta = $detallesVenta->get((int) $item['venta_detalle_id']);

                if (! $detalleVenta) {
                    throw ValidationException::withMessages([
                        'items' => ['Uno de los detalles no pertenece a la venta seleccionada.'],
                    ]);
                }

                $yaDevuelto = (float) DetalleDevolucion::query()
                    ->where('venta_detalle_id', $detalleVenta->id)
                    ->sum('cantidad');

                $cantidad = toMoney($item['cantidad'], 4);
                $disponible = toMoney((float) $detalleVenta->cantidad - $yaDevuelto, 4);

                if ($cantidad > $disponible + 0.0001) {
                    throw ValidationException::withMessages([
                        'items' => ["La cantidad a devolver de {$detalleVenta->producto->nombre} excede lo disponible ({$disponible})."],
                    ]);
                }

                $precio = toMoney($detalleVenta->precio_unitario, 4);
                $subtotal = toMoney($cantidad * $precio, 4);

                $detalleDevolucion = DetalleDevolucion::query()->create([
                    'devolucion_id' => $devolucion->id,
                    'venta_detalle_id' => $detalleVenta->id,
                    'producto_id' => $detalleVenta->producto_id,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $subtotal,
                    'motivo' => $item['motivo'] ?? null,
                ]);

                $producto = Producto::query()->lockForUpdate()->findOrFail($detalleVenta->producto_id);
                $stockAnterior = (float) $producto->stock_actual;
                $stockNuevo = toMoney($stockAnterior + $cantidad, 4);

                $producto->update([
                    'stock_actual' => $stockNuevo,
                    'mod_user' => $userId,
                ]);

                InventarioMovimiento::query()->create([
                    'producto_id' => $producto->id,
                    'venta_id' => $venta->id,
                    'venta_detalle_id' => $detalleDevolucion->venta_detalle_id,
                    'tipo' => 'devolucion_venta',
                    'cantidad' => $cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'costo_unitario' => $producto->costo_promedio,
                    'referencia' => 'DEV-'.$devolucion->id,
                    'nota' => $detalleDevolucion->motivo,
                    'add_user' => $userId,
                ]);

                if ((bool) $producto->control_vencimiento) {
                    InventarioLote::query()->create([
                        'producto_id' => $producto->id,
                        'compra_detalle_id' => null,
                        'cantidad_inicial' => $cantidad,
                        'cantidad_disponible' => $cantidad,
                        'costo_unitario' => toMoney($producto->costo_promedio, 4),
                        'fecha_vencimiento' => null,
                        'fecha_entrada' => $validated['fecha'],
                    ]);
                }

                $total += $subtotal;
            }

            $devolucion->update([
                'total' => toMoney($total, 4),
            ]);

            return $devolucion;
        });

        return response()->json([
            'message' => 'Devolucion registrada correctamente.',
            'data' => $devolucion->load([
                'venta:id,numero,fecha_venta',
                'usuario:id,name',
                'detalles.producto:id,nombre',
            ]),
        ], 201);
    }
}
