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
        Schema::table('mantenimientos', function (Blueprint $table) {
            DB::statement("ALTER TABLE mantenimientos MODIFY estado ENUM('programado', 'realizado', 'aprobado', 'rechazado') DEFAULT 'programado'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            DB::statement("ALTER TABLE mantenimientos MODIFY estado VARCHAR(30)");
        });
    }
};
