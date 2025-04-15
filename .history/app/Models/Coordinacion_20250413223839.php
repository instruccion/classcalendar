<?php

// app/Models/Coordinacion.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coordinacion extends Model
{
    protected $table = 'coordinaciones'; // <- Esto corrige el problema

    protected $fillable = ['nombre', 'descripcion', 'color', 'activa'];
}
