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
        Schema::table('materias', function (Blueprint $table) {
            $table->enum('area', [
                'humanidades',
                'matematicas', 
                'ciencias_naturales_y_educacion_ambiental',
                'ciencias_sociales',
                'ciencias_politicas_y_economicas',
                'filosofia',
                'tecnologia_e_informatica',
                'educacion_etica_y_valores_humanos',
                'educacion_religiosa',
                'educacion_artistica',
                'educacion_fisica_recreacion_y_deporte',
                'disciplina_y_convivencia_escolar',
                'dimension_comunicativa',
                'dimension_cognitiva',
                'dimension_estetica',
                'dimension_etica_y_socio_afectiva',
                'dimension_corporal'
            ])->after('descripcion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropColumn('area');
        });
    }
};
