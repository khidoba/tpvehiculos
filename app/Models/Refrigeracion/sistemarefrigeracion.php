<?php

namespace App\Models\Refrigeracion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class sistemarefrigeracion extends Model
{
    use HasFactory;

    protected $table = 'sistemarefrigeracion';

    protected $fillable = [
        'vehiculo_id',
        'tipo_temperatura',
        'tipo_refrigeracion'
    ];
/*
    protected $fillable = [
        'user_id',
        'vehiculo_id',
        'tipo_temperatura',
        'tipo_refrigeracion',
    ];
*/
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
