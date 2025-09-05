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
        Schema::create('historico_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes');
            $table->foreignId('grado_id')->constrained('grados');
            $table->integer('anio_escolar'); // 2025, 2026, etc.
            $table->string('estudiante_nombre'); // Conservar nombre
            $table->string('estudiante_apellido'); // Conservar apellido
            $table->string('estudiante_documento'); // Conservar documento
            $table->string('grado_nombre'); // Conservar nombre del grado
            $table->string('grado_grupo')->nullable(); // Conservar grupo
            $table->enum('resultado_final', ['promovido', 'reprobado', 'graduado', 'retirado'])->nullable();
            $table->decimal('promedio_anual', 4, 2)->nullable();
            $table->text('observaciones_anuales')->nullable();
            $table->timestamps();
            
            // Un estudiante solo puede estar en un grado por año
            $table->unique(['estudiante_id', 'anio_escolar']);
            $table->index(['anio_escolar', 'grado_id']);
            
            // Clave foránea hacia anios_escolares (se agregará después)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_estudiantes');
    }
};
