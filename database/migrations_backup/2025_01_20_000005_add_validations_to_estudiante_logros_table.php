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
        Schema::table('estudiante_logros', function (Blueprint $table) {
            // Agregar índice para mejorar rendimiento en consultas
            $table->index(['estudiante_id', 'periodo_id']);
            $table->index(['logro_id', 'periodo_id']);
            
            // Agregar constraint para validar que el nivel_desempeno no sea null
            $table->enum('nivel_desempeno', ['Superior', 'Alto', 'Básico', 'Bajo'])->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiante_logros', function (Blueprint $table) {
            $table->dropIndex(['estudiante_id', 'periodo_id']);
            $table->dropIndex(['logro_id', 'periodo_id']);
            $table->enum('nivel_desempeno', ['Superior', 'Alto', 'Básico', 'Bajo'])->nullable()->change();
        });
    }
}; 