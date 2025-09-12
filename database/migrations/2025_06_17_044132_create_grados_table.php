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
        Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('grupo')->nullable(); // A, B, C, etc. 
            $table->enum('tipo', ['preescolar', 'primaria', 'secundaria', 'media_academica']);
            $table->boolean('activo')->default(true);
            $table->decimal('media_academica', 3, 2)->nullable();
            $table->timestamps();
            
            // Índice único compuesto para evitar duplicados de nombre+grupo
            $table->unique(['nombre', 'grupo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grados');
    }
}; 