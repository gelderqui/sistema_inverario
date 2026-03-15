<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medida extends Model
{
    use HasFactory;

    protected $table = 'medidas';

    protected $fillable = [
        'codigo',
        'nombre',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'bool',
        ];
    }
}
