<?php

namespace App\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vehiculo\Vehiculo;
use App\Models\Refrigeracion\SistemaRefrigeracion;
use App\Models\Mantenimiento\FaseMantenimiento;
use App\Models\User;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipoMantenimiento',
        'vehiculo_id',
        'sistemarefrigeracion_id',
        'fechaInicial',
        'fechaFinal',
        'usuario_id',
        'foto_evidencia',
        'observaciones',
        'estado',
        'rechazo_razon',
        'inicio_mantenimiento',
        'diagnostico_inicial',
        'pruebas_realizadas',
        'fin_mantenimiento',
        'actividad'
    ];

    protected $casts = [
        'inicio_mantenimiento' => 'datetime',
        'fin_mantenimiento' => 'datetime',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function sistemaRefrigeracion()
    {
        return $this->belongsTo(SistemaRefrigeracion::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function materiales()
    {
        return $this->belongsToMany(Material::class, 'material_mantenimiento')
                    ->withPivot('cantidad_usada')
                    ->withTimestamps();
    }

    public static function vehiculoDisponible($vehiculo_id, $fechaInicial, $fechaFinal)
    {
        return true;
    }

    public static function usuarioDisponible($usuario_id, $fechaInicial, $fechaFinal)
    {
        return true;
    }

    public function tareas()
    {
        return $this->hasMany(TareaMantenimiento::class);
    }

    public function fases()
    {
        return $this->hasMany(FaseMantenimiento::class);
    }
}
