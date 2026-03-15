<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AjusteInventario extends Model
{
    protected $table = 'ajustes_inventario';

    protected $fillable = [
        'producto_id',
        'cantidad',
        'motivo_id',
        'usuario_id',
        'lote_id',
        'fecha',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:4',
            'fecha' => 'date',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function motivo(): BelongsTo
    {
        return $this->belongsTo(MotivoAjuste::class, 'motivo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(InventarioLote::class, 'lote_id');
    }
}
