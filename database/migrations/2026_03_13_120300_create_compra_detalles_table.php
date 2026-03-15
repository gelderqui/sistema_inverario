<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compra_detalles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('compra_id');
            $table->unsignedBigInteger('producto_id');
            $table->decimal('cantidad', 12, 4);
            $table->string('unidad_medida', 30)->nullable();
            $table->decimal('costo_unitario', 12, 4);
            $table->decimal('subtotal', 12, 4);
            $table->decimal('precio_venta_sugerido', 12, 4)->nullable();
            $table->decimal('precio_venta_aplicado', 12, 4)->nullable();
            $table->date('fecha_caducidad')->nullable();
            $table->timestamps();

            $table->foreign('compra_id')->references('id')->on('compras')->cascadeOnDelete();
            $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
            $table->index(['producto_id', 'fecha_caducidad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_detalles');
    }
};
