<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // La columna 'competencia' ya existe, no hacer nada aquí
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logros', function (Blueprint $table) {
            $table->dropColumn('competencia');
        });
    }
}; 