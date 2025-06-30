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
        Schema::dropIfExists('grado_logro');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('grado_logro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id')->constrained('grados')->onDelete('cascade');
            $table->foreignId('logro_id')->constrained('logros')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['grado_id', 'logro_id']);
        });
    }
}; 