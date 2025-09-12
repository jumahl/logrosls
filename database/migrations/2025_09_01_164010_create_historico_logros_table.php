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
        Schema::create('historico_logros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historico_desempeno_id')->constrained('historico_desempenos')->onDelete('cascade');
            $table->foreignId('logro_id')->constrained('logros');
            $table->integer('anio_escolar'); // Cambiar de year a integer para consistencia
            
            // Conservar datos del estudiante
            $table->string('estudiante_nombre');
            $table->string('estudiante_apellido');
            $table->string('estudiante_documento');
            
            // Conservar datos del logro
            $table->string('logro_descripcion');
            $table->string('materia_nombre');
            
            // Estado del logro
            $table->boolean('alcanzado')->default(true);
            
            $table->timestamps();
            
            $table->index(['historico_desempeno_id', 'logro_id']);
            $table->index(['anio_escolar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_logros');
    }
};
