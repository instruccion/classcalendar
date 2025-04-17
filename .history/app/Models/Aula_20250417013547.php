<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Aula extends Model
{
    protected $table = 'aulas';

    protected $fillable = [
        'nombre',
        'lugar',
        'capacidad',
        'videobeam',
        'computadora',
        'pizarra',
        'activa',
    ];

    protected $casts = [
        'videobeam' => 'boolean',
        'computadora' => 'boolean',
        'pizarra' => 'boolean',
        'activa' => 'boolean',
    ];
}
