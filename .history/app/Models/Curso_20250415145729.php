<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'duracion_horas',
        'coordinacion_id',
        'requiere_notificacion_inac',
    ];

    protected $casts = [
        'requiere_notificacion_inac' => 'boolean',
    ];

    // Relación de muchos a muchos con Grupo
    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_curso'); // Asegúrate de que la tabla pivote sea 'grupo_curso'
    }

    // Relación con Coordinación (uno a muchos)
    public function coordinacion()
    {
        return $this->belongsTo(Coordinacion::class);
    }
}
