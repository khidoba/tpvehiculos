<?php

namespace App\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaseMantenimiento extends Model
{
    use HasFactory;

    protected $table = 'fases_mantenimiento';

    protected $fillable = [
        'mantenimiento_id',
        'tipo_fase',
        'fecha_hora',
        'descripcion',
        'acciones_realizadas',
        'observaciones',
        'foto_evidencia',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class);
    }

    public function materiales()
    {
        return $this->belongsToMany(Material::class, 'fase_mantenimiento_material')
                    ->withPivot('cantidad_usada')
                    ->withTimestamps();
    }
}
