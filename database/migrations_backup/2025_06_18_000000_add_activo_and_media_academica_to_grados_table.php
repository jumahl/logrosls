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
        Schema::table('grados', function (Blueprint $table) {
            // Agregar el campo 'activo' si no existe
            if (!Schema::hasColumn('grados', 'activo')) {
                $table->boolean('activo')->default(true)->after('tipo');
            }
        });

        // Cambiar el enum 'tipo' para agregar 'media_academica'
        // Nota: Esto requiere doctrine/dbal instalado
        Schema::table('grados', function (Blueprint $table) {
            $table->enum('tipo', ['preescolar', 'primaria', 'secundaria', 'media_academica'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grados', function (Blueprint $table) {
            if (Schema::hasColumn('grados', 'activo')) {
                $table->dropColumn('activo');
            }
            $table->enum('tipo', ['preescolar', 'primaria', 'secundaria'])->change();
        });
    }
}; 