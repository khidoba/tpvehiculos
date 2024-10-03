<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mantenimiento\Mantenimiento;

class TareaMantenimiento extends Model
{
    use HasFactory;

    protected $fillable = ['mantenimiento_id', 'descripcion'];

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class);
    }
}
