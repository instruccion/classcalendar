<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'coordinacion_id'
    ];

    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'grupo_curso');
    }

    public function coordinacion()
    {
        return $this->belongsTo(Coordinacion::class);
    }
}
