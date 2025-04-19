<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\Instructor;

class Curso extends Model
{
    use HasFactory, LogsActivity;

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

    // Auditoría Spatie
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('curso')
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Curso fue {$eventName}");
    }

    // Relación de muchos a muchos con Grupo
    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_curso');
    }

    // Relación con Coordinación
    public function coordinacion()
    {
        return $this->belongsTo(Coordinacion::class);
    }

    
}
