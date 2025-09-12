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
        Schema::create('desempenos_materia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->foreignId('periodo_id')->constrained('periodos')->onDelete('cascade');
            $table->enum('nivel_desempeno', ['E', 'S', 'A', 'I']);
            $table->text('observaciones_finales')->nullable();
            $table->date('fecha_asignacion');
            $table->enum('estado', ['borrador', 'publicado', 'revisado'])->default('borrador');
            $table->datetime('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Índice único para evitar duplicados
            $table->unique(['estudiante_id', 'materia_id', 'periodo_id'], 'unique_estudiante_materia_periodo');
            
            // Índices para mejorar rendimiento en consultas comunes
            $table->index(['estudiante_id', 'periodo_id']);
            $table->index(['materia_id', 'periodo_id']);
            $table->index(['periodo_id', 'nivel_desempeno']);
            $table->index(['estado']);
            $table->index(['fecha_asignacion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desempenos_materia');
    }
};
