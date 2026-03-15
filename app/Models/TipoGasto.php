<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoGasto extends Model
{
    protected $table = 'tipos_gasto';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'bool',
        ];
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'tipo_gasto_id');
    }
}
