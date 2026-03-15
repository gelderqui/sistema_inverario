<?php

namespace Database\Seeders;

use App\Models\Medida;
use Illuminate\Database\Seeder;

class MedidaSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['codigo' => 'unidad', 'nombre' => 'Unidad'],
            ['codigo' => 'lb', 'nombre' => 'Libra'],
            ['codigo' => 'kg', 'nombre' => 'Kilogramo'],
            ['codigo' => 'g', 'nombre' => 'Gramo'],
            ['codigo' => 'onza', 'nombre' => 'Onza'],
            ['codigo' => 'qq', 'nombre' => 'Quintal'],
            ['codigo' => 'litro', 'nombre' => 'Litro'],
            ['codigo' => 'ml', 'nombre' => 'Mililitro'],
            ['codigo' => 'galon', 'nombre' => 'Galon'],
            ['codigo' => 'caja', 'nombre' => 'Caja'],
            ['codigo' => 'paquete', 'nombre' => 'Paquete'],
            ['codigo' => 'docena', 'nombre' => 'Docena'],
            ['codigo' => 'botella', 'nombre' => 'Botella'],
            ['codigo' => 'lata', 'nombre' => 'Lata'],
            ['codigo' => 'bolsa', 'nombre' => 'Bolsa'],
        ];

        foreach ($items as $item) {
            Medida::query()->updateOrCreate(
                ['codigo' => $item['codigo']],
                [
                    'nombre' => $item['nombre'],
                    'activo' => true,
                ]
            );
        }
    }
}
