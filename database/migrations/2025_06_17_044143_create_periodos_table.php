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
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->enum('corte', ['Primer Corte', 'Segundo Corte']);
            $table->integer('año_escolar');
            $table->integer('numero_periodo'); // 1 o 2 para el año escolar
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activo')->default(false);
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index(['año_escolar', 'numero_periodo', 'corte']);
            $table->index(['activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
}; 