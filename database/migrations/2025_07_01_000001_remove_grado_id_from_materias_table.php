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
            // Verificar si la columna existe antes de intentar eliminarla
            if (Schema::hasColumn('materias', 'grado_id')) {
                // Verificar si la clave foránea existe antes de eliminarla
                $foreignKeys = Schema::getConnection()
                    ->getDoctrineSchemaManager()
                    ->listTableForeignKeys('materias');
                
                $foreignKeyExists = false;
                foreach ($foreignKeys as $foreignKey) {
                    if (in_array('grado_id', $foreignKey->getLocalColumns())) {
                        $foreignKeyExists = true;
                        break;
                    }
                }
                
                if ($foreignKeyExists) {
                    $table->dropForeign(['grado_id']);
                }
                
                $table->dropColumn('grado_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            if (!Schema::hasColumn('materias', 'grado_id')) {
                $table->foreignId('grado_id')->constrained('grados')->after('codigo');
            }
        });
    }
}; 