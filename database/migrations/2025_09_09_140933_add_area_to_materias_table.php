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
                'matemáticas',
                'ciencias_naturales_y_educacion_ambiental',
                'ciencias_sociales',
                'ciencias_políticas_y_económicas',
                'filosofía',
                'tecnología_e_informática',
                'educación_ética_y_valores_humanos',
                'educación_religiosa',
                'educación_artística',
                'educación_física_recreación_y_deporte',
                'disciplina_y_convivencia_escolar',
                'dimensión_comunicativa',
                'dimensión_cognitiva',
                'dimensión_estética',
                'dimensión_ética_y_socio_afectiva',
                'dimensión_corporal',
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
