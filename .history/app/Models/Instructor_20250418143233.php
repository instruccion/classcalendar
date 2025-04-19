<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Collection;
use App\Models\Documento;

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
    protected static $logAttributes = ['nombre', 'especialidad', 'correo', 'telefono'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(self::$logAttributes)
            ->setDescriptionForEvent(fn(string $eventName) => "Instructor {$eventName}");
    }

    // Relación: coordinaciones asignadas
    public function coordinaciones()
    {
        return $this->belongsToMany(Coordinacion::class, 'coordinacion_instructor');
    }

    // Relación: cursos que puede dictar
    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'curso_instructor');
    }

    // Relación: documentos del instructor
    public function documentos()
    {
        return $this->belongsToMany(Documento::class, 'documento_instructor')
                    ->withPivot('id', 'fecha_vencimiento') // <--- ¡VERIFICA ESTO!
                    ->withTimestamps(); // Si también tienes timestamps en la pivote
    }

    // ✅ Documentos vencidos (componentes y habilitaciones)
    public function documentosVencidos(): Collection
    {
        return $this->documentos->filter(function ($doc) {
            return $doc->pivot->fecha_vencimiento && now()->greaterThan($doc->pivot->fecha_vencimiento);
        });
    }

    // ✅ Saber si tiene al menos un documento vencido
    public function tieneDocumentosVencidos(): bool
    {
        return $this->documentosVencidos()->isNotEmpty();
    }

    // ✅ Accesor opcional para mostrar en rojo si tiene vencimientos
    public function getNombreConEstadoAttribute(): string
    {
        if ($this->tieneDocumentosVencidos()) {
            return '<span class="text-red-600 font-semibold">' . e($this->nombre) . '</span>';
        }

        return e($this->nombre);
    }
}
