<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra_detalles', function (Blueprint $table): void {
            $table->string('unidad_medida', 30)->nullable()->after('cantidad');
        });
    }

    public function down(): void
    {
        Schema::table('compra_detalles', function (Blueprint $table): void {
            $table->dropColumn('unidad_medida');
        });
    }
};
