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
        if (!Schema::hasTable('fases_mantenimiento')) {
            Schema::create('fases_mantenimiento', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mantenimiento_id')->constrained('mantenimientos')->onDelete('cascade');
                $table->enum('tipo_fase', ['diagnostico', 'ejecucion', 'entrega']);
                $table->dateTime('hora_inicio');
                $table->dateTime('hora_fin')->nullable();
                $table->text('descripcion');
                $table->text('acciones_realizadas');
                $table->text('observaciones')->nullable();
                $table->string('foto_evidencia')->nullable();
                $table->timestamps();

                // Índices con nombres más cortos
                $table->index('mantenimiento_id', 'fm_mantenimiento_id_index');
                $table->index('tipo_fase', 'fm_tipo_fase_index');
            });
        }

        if (!Schema::hasTable('fase_mantenimiento_material')) {
            Schema::create('fase_mantenimiento_material', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fase_mantenimiento_id')->constrained('fases_mantenimiento')->onDelete('cascade');
                $table->foreignId('material_id')->constrained('materiales')->onDelete('cascade');
                $table->integer('cantidad_usada');
                $table->timestamps();

                // Índice con nombre más corto
                $table->index(['fase_mantenimiento_id', 'material_id'], 'fmm_fase_material_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fase_mantenimiento_material');
        Schema::dropIfExists('fases_mantenimiento');
    }
};
