<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coordinacion extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'color', 'activa'];
}
