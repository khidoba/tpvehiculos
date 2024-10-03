<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMantenimientosTable extends Migration
{
    public function up()
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->constrained('vehiculos');
            $table->enum('tipoMantenimiento', ['preventivo', 'correctivo']);
            $table->foreignId('sistemarefrigeracion_id')->nullable()->constrained('sistemarefrigeracion');
            $table->date('fechaInicial');
            $table->date('fechaFinal');
            $table->foreignId('usuario_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mantenimientos');
    }
}
