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
            $table->dateTime('inicio_mantenimiento')->nullable()->after('fechaInicial');
            $table->text('diagnostico_inicial')->nullable()->after('inicio_mantenimiento');
            $table->text('pruebas_realizadas')->nullable()->after('diagnostico_inicial');
            $table->dateTime('fin_mantenimiento')->nullable()->after('pruebas_realizadas');
        });

        // Crear tabla para tareas de mantenimiento
        Schema::create('tarea_mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mantenimiento_id');
            $table->string('descripcion');
            $table->timestamps();

            $table->foreign('mantenimiento_id')->references('id')->on('mantenimientos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropColumn(['inicio_mantenimiento', 'diagnostico_inicial', 'pruebas_realizadas', 'fin_mantenimiento']);
        });

        Schema::dropIfExists('tarea_mantenimientos');
    }
};
