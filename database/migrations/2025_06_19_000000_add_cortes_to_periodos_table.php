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
        Schema::table('periodos', function (Blueprint $table) {
            $table->enum('corte', ['Primer Corte', 'Segundo Corte'])->after('nombre');
            $table->integer('año_escolar')->after('corte');
            $table->integer('numero_periodo')->after('año_escolar'); // 1 o 2 para el año escolar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periodos', function (Blueprint $table) {
            $table->dropColumn(['corte', 'año_escolar', 'numero_periodo']);
        });
    }
}; 