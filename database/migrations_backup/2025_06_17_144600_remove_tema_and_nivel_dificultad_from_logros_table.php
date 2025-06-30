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
            $table->dropColumn(['tema', 'nivel_dificultad']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logros', function (Blueprint $table) {
            $table->string('tema')->after('competencia');
            $table->enum('nivel_dificultad', ['bajo', 'medio', 'alto'])->default('medio')->after('descripcion');
        });
    }
}; 