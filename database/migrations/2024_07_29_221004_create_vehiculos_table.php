<?php
// Migración para la tabla Vehiculo
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('agencia', 50);
            $table->string('vehiculo');
            $table->string('placa', 20)->unique();
            $table->string('marca', 50);
            $table->string('modelo', 50);
            $table->year('anio');
            $table->decimal('dimension_pies', 5, 2);
            $table->string('estado', 30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
