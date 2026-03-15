<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->unsignedBigInteger('proveedor_id')->nullable()->after('categoria_id');
            $table->decimal('precio_venta', 12, 4)->default(0)->after('palabras_clave');
            $table->decimal('costo_promedio', 12, 4)->default(0)->after('precio_venta');
            $table->decimal('stock_actual', 12, 4)->default(0)->after('costo_promedio');
            $table->decimal('stock_minimo', 12, 4)->default(0)->after('stock_actual');
            $table->unsignedBigInteger('unidad_medida_id')->nullable()->after('stock_minimo');
            $table->boolean('control_vencimiento')->default(false)->after('unidad_medida_id');
            $table->unsignedSmallInteger('dias_alerta_vencimiento')->default(15)->after('control_vencimiento');
            $table->decimal('peso_referencial', 12, 4)->nullable()->after('dias_alerta_vencimiento');

            $table->foreign('proveedor_id')->references('id')->on('proveedores')->nullOnDelete();
            $table->index(['activo', 'stock_actual'], 'productos_activo_stock_idx');
            $table->index('proveedor_id');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->dropForeign(['proveedor_id']);
            $table->dropIndex('productos_activo_stock_idx');
            $table->dropIndex(['proveedor_id']);
            $table->dropColumn([
                'proveedor_id',
                'precio_venta',
                'costo_promedio',
                'stock_actual',
                'stock_minimo',
                'unidad_medida_id',
                'control_vencimiento',
                'dias_alerta_vencimiento',
                'peso_referencial',
            ]);
        });
    }
};
