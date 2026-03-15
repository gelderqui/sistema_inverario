<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motivos_ajuste', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('tipo', 20)->default('ambos');
            $table->timestamps();
        });

        Schema::create('ajustes_inventario', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->decimal('cantidad', 12, 4);
            $table->unsignedBigInteger('motivo_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('lote_id')->nullable();
            $table->date('fecha');
            $table->string('observacion', 255)->nullable();
            $table->timestamps();

            $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
            $table->foreign('motivo_id')->references('id')->on('motivos_ajuste')->restrictOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('lote_id')->references('id')->on('inventario_lotes')->nullOnDelete();
            $table->index(['fecha', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustes_inventario');
        Schema::dropIfExists('motivos_ajuste');
    }
};
