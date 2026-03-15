<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table): void {
            $table->unsignedBigInteger('last_modified_by_user_id')->nullable()->after('updated_at');
            $table->string('last_modified_by_user_name', 255)->nullable()->after('last_modified_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table): void {
            $table->dropColumn(['last_modified_by_user_id', 'last_modified_by_user_name']);
        });
    }
};
