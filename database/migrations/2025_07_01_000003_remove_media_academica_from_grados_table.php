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
            if (Schema::hasColumn('grados', 'media_academica')) {
                $table->dropColumn('media_academica');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grados', function (Blueprint $table) {
            if (!Schema::hasColumn('grados', 'media_academica')) {
                $table->decimal('media_academica', 3, 2)->nullable();
            }
        });
    }
}; 