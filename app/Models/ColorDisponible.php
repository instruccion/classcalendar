<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColorDisponible extends Model
{
    protected $table = 'colores_disponibles';

    public $timestamps = false;

    protected $fillable = [
        'color',
        'disponible',
    ];
}
