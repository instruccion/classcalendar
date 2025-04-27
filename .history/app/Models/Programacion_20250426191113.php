<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Programacion extends Model
{
    use HasFactory;

    protected $table = 'programaciones';

    protected $fillable = [
        'curso_id',
        'grupo_id',
        'instructor_id',
        'aula_id',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'notificado_inac',
        'fecha_notificacion_inac',
        'estado',
        'bloque_codigo',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime:Y-m-d',
        'fecha_fin' => 'datetime:Y-m-d',
        'fecha_notificacion_inac' => 'date',
        // ⚠️ IMPORTANTE: hora_inicio y hora_fin las QUITAMOS de aquí, así Laravel no intenta interpretarlas
        'notificado_inac' => 'boolean',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class);
    }

    public function getInicioCompletoAttribute(): ?Carbon
    {
        if ($this->fecha_inicio && $this->hora_inicio) {
            try {
                return Carbon::parse($this->fecha_inicio->format('Y-m-d') . ' ' . $this->hora_inicio);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function getFinCompletoAttribute(): ?Carbon
    {
        if ($this->fecha_fin && $this->hora_fin) {
            try {
                return Carbon::parse($this->fecha_fin->format('Y-m-d') . ' ' . $this->hora_fin);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }
}
