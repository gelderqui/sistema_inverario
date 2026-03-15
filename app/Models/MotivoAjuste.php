<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotivoAjuste extends Model
{
    protected $table = 'motivos_ajuste';

    protected $fillable = [
        'nombre',
        'tipo',
    ];

    public function ajustes(): HasMany
    {
        return $this->hasMany(AjusteInventario::class, 'motivo_id');
    }
}
