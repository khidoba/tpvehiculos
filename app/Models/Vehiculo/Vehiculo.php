<?php

namespace App\Models\Vehiculo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Refrigeracion\sistemarefrigeracion;
use App\Models\Mantenimiento\Mantenimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'agencia',
        'vehiculo',
        'placa',
        'marca',
        'modelo',
        'anio',
        'dimension_pies',
        'estado'
    ];

    protected $casts = [
        'anio' => 'integer',
        'dimension_pies' => 'decimal:2'
    ];

    // Mutator para asegurar que 'anio' se guarde correctamente
    public function setAnioAttribute($value)
    {
        $this->attributes['anio'] = (int) $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sistemaRefrigeracion()
    {
        return $this->hasOne(sistemarefrigeracion::class);
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class);
    }

    public function tecnicos()
    {
        return $this->belongsToMany(User::class, 'vehiculo_tecnico', 'vehiculo_id', 'user_id');
    }
}
