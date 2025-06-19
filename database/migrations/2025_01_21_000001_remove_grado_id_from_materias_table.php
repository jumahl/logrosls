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
            // Eliminar la restricción de clave foránea primero
            $table->dropForeign(['grado_id']);
            // Eliminar la columna grado_id
            $table->dropColumn('grado_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->foreignId('grado_id')->constrained('grados')->after('codigo');
        });
    }
}; 