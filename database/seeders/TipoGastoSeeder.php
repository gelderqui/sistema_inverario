<?php

namespace Database\Seeders;

use App\Models\TipoGasto;
use Illuminate\Database\Seeder;

class TipoGastoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nombre' => 'Pago de empleado', 'descripcion' => 'Sueldos y pagos de personal.'],
            ['nombre' => 'Internet', 'descripcion' => 'Servicio mensual de internet.'],
            ['nombre' => 'Agua', 'descripcion' => 'Servicio de agua potable.'],
            ['nombre' => 'Luz', 'descripcion' => 'Servicio de energia electrica.'],
            ['nombre' => 'Alquiler', 'descripcion' => 'Renta del local.'],
            ['nombre' => 'Mantenimiento', 'descripcion' => 'Mantenimiento y reparaciones.'],
            ['nombre' => 'Equipo de computo', 'descripcion' => 'Compra o reposicion de equipo de computo.'],
            ['nombre' => 'Transporte', 'descripcion' => 'Gastos de transporte y fletes.'],
            ['nombre' => 'Otros', 'descripcion' => 'Gastos varios.'],
        ];

        foreach ($items as $item) {
            TipoGasto::query()->updateOrCreate(
                ['nombre' => $item['nombre']],
                [
                    'descripcion' => $item['descripcion'],
                    'activo' => true,
                ]
            );
        }
    }
}
