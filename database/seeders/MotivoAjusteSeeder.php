<?php

namespace Database\Seeders;

use App\Models\MotivoAjuste;
use Illuminate\Database\Seeder;

class MotivoAjusteSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nombre' => 'Producto vencido', 'tipo' => 'salida'],
            ['nombre' => 'Producto dañado', 'tipo' => 'salida'],
            ['nombre' => 'Perdida', 'tipo' => 'salida'],
            ['nombre' => 'Ajuste inventario', 'tipo' => 'ambos'],
            ['nombre' => 'Error conteo', 'tipo' => 'ambos'],
        ];

        foreach ($items as $item) {
            MotivoAjuste::query()->updateOrCreate(
                ['nombre' => $item['nombre']],
                ['tipo' => $item['tipo']]
            );
        }
    }
}