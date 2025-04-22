<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GrupoCurso extends Model
{
    use LogsActivity;

    protected $table = 'grupo_curso';

    protected $fillable = [
        'grupo_id',
        'curso_id',
    ];

    public $timestamps = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('grupo_curso')
            ->logOnly(['grupo_id', 'curso_id'])
            ->setDescriptionForEvent(function(string $eventName) {
                return "Relación grupo_curso fue {$eventName}";
            });
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}
