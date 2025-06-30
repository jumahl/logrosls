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
        Schema::create('estudiante_logros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->foreignId('logro_id')->constrained('logros');
            $table->foreignId('periodo_id')->constrained('periodos');
            $table->enum('nivel_desempeno', ['Superior', 'Alto', 'Básico', 'Bajo']);
            $table->text('observaciones')->nullable();
            $table->date('fecha_asignacion');
            $table->timestamps();

            // Asegurar que un estudiante no tenga el mismo logro en el mismo periodo
            $table->unique(['estudiante_id', 'logro_id', 'periodo_id']);
            
            // Índices para mejorar rendimiento
            $table->index(['estudiante_id', 'periodo_id']);
            $table->index(['logro_id', 'periodo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiante_logros');
    }
}; 