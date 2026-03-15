<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_movimientos', function (Blueprint $table): void {
            $table->unsignedBigInteger('venta_id')->nullable()->after('compra_detalle_id');
            $table->unsignedBigInteger('venta_detalle_id')->nullable()->after('venta_id');

            $table->foreign('venta_id')->references('id')->on('ventas')->nullOnDelete();
            $table->foreign('venta_detalle_id')->references('id')->on('venta_detalles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventario_movimientos', function (Blueprint $table): void {
            $table->dropForeign(['venta_id']);
            $table->dropForeign(['venta_detalle_id']);
            $table->dropColumn(['venta_id', 'venta_detalle_id']);
        });
    }
};
