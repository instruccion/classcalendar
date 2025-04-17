<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Instructor extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'instructores';

    protected $fillable = [
        'nombre',
        'especialidad',
        'correo',
        'telefono',
    ];


    protected static $logName = 'instructor';
    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(self::$logAttributes)
            ->setDescriptionForEvent(fn(string $eventName) => "Instructor {$eventName}");
    }

    protected static $logAttributes = ['nombre', 'especialidad', 'coordinacion_id'];

    // Relación: un instructor pertenece a una coordinación
    public function coordinaciones()
    {
        return $this->belongsToMany(Coordinacion::class, 'coordinacion_instructor');
    }

    // Relación: un instructor puede dar muchos cursos (muchos a muchos)
    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'curso_instructor');
    }

    
}
