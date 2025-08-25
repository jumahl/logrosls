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
            $table->foreignId('logro_id')->constrained('logros')->onDelete('cascade');
            $table->foreignId('desempeno_materia_id')->constrained('desempenos_materia')->onDelete('cascade');
            $table->boolean('alcanzado')->default(true);
            $table->timestamps();

            // Índices para mejorar rendimiento
            $table->index(['desempeno_materia_id', 'logro_id']);
            $table->index(['logro_id']);
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