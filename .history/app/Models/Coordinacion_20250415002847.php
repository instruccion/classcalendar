<?php

// app/Models/Coordinacion.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coordinacion extends Model
{
    protected $table = 'coordinaciones';

    protected $fillable = ['nombre', 'descripcion', 'color', 'activa'];

    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }

}
