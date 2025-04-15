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

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_curso');
    }

    public function coordinacion()
    {
        return $this->belongsTo(Coordinacion::class);
    }
}
