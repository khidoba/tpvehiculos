<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        if (!Schema::hasTable('vehiculo_tecnico')) {
            Schema::create('vehiculo_tecnico', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vehiculo_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->foreign('vehiculo_id')->references('id')->on('vehiculos')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculo_tecnico');
    }
};
