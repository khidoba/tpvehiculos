<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateFasesMantenimientoTable extends Migration
{
    public function up()
    {
        Schema::table('fases_mantenimiento', function (Blueprint $table) {
            $table->dateTime('fecha_hora')->nullable()->after('tipo_fase');
        });

        // Actualizar registros existentes
        DB::table('fases_mantenimiento')
            ->whereNull('fecha_hora')
            ->update(['fecha_hora' => DB::raw('COALESCE(hora_inicio, created_at)')]);

        Schema::table('fases_mantenimiento', function (Blueprint $table) {
            $table->dateTime('fecha_hora')->nullable(false)->change();
            $table->dropColumn(['hora_inicio', 'hora_fin']);
        });
    }

    public function down()
    {
        Schema::table('fases_mantenimiento', function (Blueprint $table) {
            $table->dateTime('hora_inicio')->nullable()->after('tipo_fase');
            $table->dateTime('hora_fin')->nullable()->after('hora_inicio');
            $table->dropColumn('fecha_hora');
        });
    }
}
