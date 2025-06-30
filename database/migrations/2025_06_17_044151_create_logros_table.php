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
        Schema::create('logros', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->string('titulo');
            $table->string('competencia');
            $table->string('tema');
            $table->string('indicador_desempeno');
            $table->text('descripcion')->nullable();
            $table->enum('nivel_dificultad', ['bajo', 'medio', 'alto'])->default('medio');
            $table->enum('tipo', ['conocimiento', 'habilidad', 'actitud', 'valor'])->default('conocimiento');
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->string('dimension')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logros');
    }
}; 