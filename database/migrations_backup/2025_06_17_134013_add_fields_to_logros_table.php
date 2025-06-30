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
        Schema::table('logros', function (Blueprint $table) {
            // Verificar si la columna codigo ya existe
            if (!Schema::hasColumn('logros', 'codigo')) {
                $table->string('codigo')->unique()->after('id');
            }
            
            // Agregar un campo para la descripción detallada
            $table->text('descripcion')->nullable()->after('indicador_desempeno');
            
            // Agregar un campo para el nivel de dificultad
            $table->enum('nivel_dificultad', ['bajo', 'medio', 'alto'])->default('medio')->after('descripcion');
            
            // Agregar un campo para el tipo de logro
            $table->enum('tipo', ['conocimiento', 'habilidad', 'actitud'])->default('conocimiento')->after('nivel_dificultad');
            
            // Agregar un campo para el estado del logro
            $table->boolean('activo')->default(true)->after('tipo');
            
            // Agregar un campo para el orden de presentación
            $table->integer('orden')->default(0)->after('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logros', function (Blueprint $table) {
            $table->dropColumn([
                'descripcion',
                'nivel_dificultad',
                'tipo',
                'activo',
                'orden'
            ]);
        });
    }
};
