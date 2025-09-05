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
        Schema::create('historico_desempenos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->foreignId('materia_id')->constrained('materias');
            $table->foreignId('periodo_id')->constrained('periodos');
            $table->integer('anio_escolar'); // Para facilitar consultas
            
            // Conservar datos del estudiante
            $table->string('estudiante_nombre');
            $table->string('estudiante_apellido');
            $table->string('estudiante_documento');
            
            // Conservar datos de la materia
            $table->string('materia_nombre');
            $table->string('materia_codigo');
            
            // Conservar datos del período
            $table->string('periodo_nombre');
            $table->enum('periodo_corte', ['Primer Corte', 'Segundo Corte']);
            $table->integer('periodo_numero');
            
            // Datos del desempeño
            $table->enum('nivel_desempeno', ['E', 'S', 'A', 'I']);
            $table->text('observaciones_finales')->nullable();
            
            // Conservar información del docente y director
            $table->string('docente_nombre')->nullable();
            $table->string('director_grupo')->nullable();
            
            $table->timestamps();
            
            $table->index(['estudiante_id', 'anio_escolar']);
            $table->index(['anio_escolar', 'materia_id']);
            $table->index(['anio_escolar', 'periodo_id']);
            
            // Clave foránea hacia anios_escolares (se agregará después)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_desempenos');
    }
};
