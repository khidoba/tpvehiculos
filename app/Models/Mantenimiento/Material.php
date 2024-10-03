<?php

namespace App\Models\Mantenimiento;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Material extends Model
{
    use HasFactory;

    protected $table = 'materiales';

    protected $fillable = [
        'nombre',
        'cantidad',
        'tipo',
        'categoria',
        'costo'
    ];

    public function mantenimientos()
    {
        return $this->belongsToMany(Mantenimiento::class, 'material_mantenimiento')
                    ->withPivot('cantidad_usada')
                    ->withTimestamps();
    }
}
