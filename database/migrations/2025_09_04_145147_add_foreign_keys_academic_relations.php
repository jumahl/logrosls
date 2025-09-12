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
        // Agregar claves foráneas DESPUÉS de que todas las tablas estén creadas
        
        // 1. Clave foránea de periodos a anios_escolares
        Schema::table('periodos', function (Blueprint $table) {
            $table->foreign('anio_escolar')
                  ->references('anio')
                  ->on('anios_escolares')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        // 2. Clave foránea de historico_estudiantes a anios_escolares
        Schema::table('historico_estudiantes', function (Blueprint $table) {
            $table->foreign('anio_escolar')
                  ->references('anio')
                  ->on('anios_escolares')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        // 3. Clave foránea de historico_desempenos a anios_escolares
        Schema::table('historico_desempenos', function (Blueprint $table) {
            $table->foreign('anio_escolar')
                  ->references('anio')
                  ->on('anios_escolares')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        // 4. Agregar FK de historico_logros a anios_escolares
        Schema::table('historico_logros', function (Blueprint $table) {
            $table->foreign('anio_escolar')
                  ->references('anio')
                  ->on('anios_escolares')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar claves foráneas en orden inverso
        Schema::table('historico_logros', function (Blueprint $table) {
            $table->dropForeign(['anio_escolar']);
        });

        Schema::table('historico_desempenos', function (Blueprint $table) {
            $table->dropForeign(['anio_escolar']);
        });

        Schema::table('historico_estudiantes', function (Blueprint $table) {
            $table->dropForeign(['anio_escolar']);
        });

        Schema::table('periodos', function (Blueprint $table) {
            $table->dropForeign(['anio_escolar']);
        });
    }
};
