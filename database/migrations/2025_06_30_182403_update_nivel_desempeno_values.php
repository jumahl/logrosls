<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero actualizar los datos existentes
        DB::table('estudiante_logros')->where('nivel_desempeno', 'Superior')->update(['nivel_desempeno' => 'E']);
        DB::table('estudiante_logros')->where('nivel_desempeno', 'Alto')->update(['nivel_desempeno' => 'S']);
        DB::table('estudiante_logros')->where('nivel_desempeno', 'Básico')->update(['nivel_desempeno' => 'A']);
        DB::table('estudiante_logros')->where('nivel_desempeno', 'Bajo')->update(['nivel_desempeno' => 'I']);

        // Luego modificar la estructura del enum
        Schema::table('estudiante_logros', function (Blueprint $table) {
            $table->enum('nivel_desempeno', ['E', 'S', 'A', 'I'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los datos
        DB::table('estudiante_logros')->where('nivel_desempeno', 'E')->update(['nivel_desempeno' => 'Superior']);
        DB::table('estudiante_logros')->where('nivel_desempeno', 'S')->update(['nivel_desempeno' => 'Alto']);
        DB::table('estudiante_logros')->where('nivel_desempeno', 'A')->update(['nivel_desempeno' => 'Básico']);
        DB::table('estudiante_logros')->where('nivel_desempeno', 'I')->update(['nivel_desempeno' => 'Bajo']);

        // Revertir la estructura del enum
        Schema::table('estudiante_logros', function (Blueprint $table) {
            $table->enum('nivel_desempeno', ['Superior', 'Alto', 'Básico', 'Bajo'])->change();
        });
    }
};
