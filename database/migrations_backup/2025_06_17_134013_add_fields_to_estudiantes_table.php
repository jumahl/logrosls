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
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->string('apellido')->after('nombre');
            $table->string('direccion')->nullable()->after('fecha_nacimiento');
            $table->string('telefono')->nullable()->after('direccion');
            $table->string('email')->nullable()->after('telefono');
            $table->boolean('activo')->default(true)->after('grado_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn([
                'apellido',
                'direccion',
                'telefono',
                'email',
                'activo'
            ]);
        });
    }
}; 