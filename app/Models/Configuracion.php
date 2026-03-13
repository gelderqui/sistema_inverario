<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'codigo',
        'descripcion',
        'value',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'bool',
        ];
    }

    public static function valor(string $codigo, mixed $default = null): mixed
    {
        return static::query()
            ->where('codigo', $codigo)
            ->where('activo', true)
            ->value('value') ?? $default;
    }
}
