<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    protected $table = 'gastos';

    protected $fillable = [
        'tipo_gasto_id',
        'descripcion',
        'monto',
        'fecha',
        'usuario_id',
        'metodo_pago',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:4',
            'fecha' => 'date',
        ];
    }

    public function tipoGasto(): BelongsTo
    {
        return $this->belongsTo(TipoGasto::class, 'tipo_gasto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
