<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Grupo extends Model
{
    use LogsActivity;

    protected $table = 'grupos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'coordinacion_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('grupo')
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Grupo fue {$eventName}");
    }

    // Relación con Coordinación (muchos a uno)
    public function coordinacion(): BelongsTo
    {
        return $this->belongsTo(Coordinacion::class);
    }

    // Relación con Cursos (muchos a muchos)
    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'curso_grupo', 'grupo_id', 'curso_id');


    }
}
