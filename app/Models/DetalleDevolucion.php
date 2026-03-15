<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleDevolucion extends Model
{
    protected $table = 'detalle_devoluciones';

    protected $fillable = [
        'devolucion_id',
        'venta_detalle_id',
        'producto_id',
        'cantidad',
        'precio',
        'subtotal',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:4',
            'precio' => 'decimal:4',
            'subtotal' => 'decimal:4',
        ];
    }

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(Devolucion::class, 'devolucion_id');
    }

    public function ventaDetalle(): BelongsTo
    {
        return $this->belongsTo(VentaDetalle::class, 'venta_detalle_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
