<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table): void {
            if (! Schema::hasColumn('ventas', 'estado')) {
                $table->string('estado', 20)->default('activo')->after('fecha_venta');
                $table->index('estado');
            }
        });

        Schema::table('devoluciones', function (Blueprint $table): void {
            if (! Schema::hasColumn('devoluciones', 'estado')) {
                $table->string('estado', 20)->default('activo')->after('fecha');
                $table->index('estado');
            }
        });

        DB::table('ventas')->whereNull('estado')->update(['estado' => 'activo']);
        DB::table('devoluciones')->whereNull('estado')->update(['estado' => 'activo']);

        DB::table('compras')->where('estado', 'registrada')->update(['estado' => 'activo']);
    }

    public function down(): void
    {
        Schema::table('devoluciones', function (Blueprint $table): void {
            if (Schema::hasColumn('devoluciones', 'estado')) {
                $table->dropIndex(['estado']);
                $table->dropColumn('estado');
            }
        });

        Schema::table('ventas', function (Blueprint $table): void {
            if (Schema::hasColumn('ventas', 'estado')) {
                $table->dropIndex(['estado']);
                $table->dropColumn('estado');
            }
        });

        DB::table('compras')->where('estado', 'activo')->update(['estado' => 'registrada']);
    }
};
