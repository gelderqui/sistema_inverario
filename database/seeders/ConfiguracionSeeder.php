<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        Configuracion::query()->where('codigo', 'locale')->delete();

        $items = [
            [
                'codigo' => 'nombre_empresa',
                'descripcion' => 'Nombre comercial mostrado en el sistema.',
                'value' => 'weltixh',
                'activo' => true,
            ],
            [
                'codigo' => 'tiempo_sesion',
                'descripcion' => 'Tiempo en sesion inactiva cuando se marca "Mantener sesion iniciada". Si no se marca, la sesion se mantiene por 120 minutos.',
                'value' => '120',
                'activo' => true,
            ],
        ];

        foreach ($items as $item) {
            Configuracion::query()->updateOrCreate(
                ['codigo' => $item['codigo']],
                $item
            );
        }
    }
}
