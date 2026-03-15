<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('descripcion')->nullable();
            $table->text('value')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unsignedBigInteger('last_modified_by_user_id')->nullable();
            $table->string('last_modified_by_user_name', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
