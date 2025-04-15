<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grupo extends Model
{
    protected $table = 'grupos'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'nombre',
        'descripcion',
        'coordinacion_id',
    ];

    // Relación con Coordinación (muchos a uno)
    public function coordinacion(): BelongsTo
    {
        return $this->belongsTo(Coordinacion::class);
    }

    // Relación con Cursos (muchos a muchos)
    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'grupo_curso');
    }
}
