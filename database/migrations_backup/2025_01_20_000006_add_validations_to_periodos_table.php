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
            // Agregar constraint para validar que fecha_fin sea mayor que fecha_inicio
            // Esto se hará a nivel de aplicación ya que MySQL no soporta check constraints fácilmente
            
            // Agregar índice para mejorar rendimiento
            $table->index(['año_escolar', 'numero_periodo', 'corte']);
            $table->index(['activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periodos', function (Blueprint $table) {
            $table->dropIndex(['año_escolar', 'numero_periodo', 'corte']);
            $table->dropIndex(['activo']);
        });
    }
}; 