<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Feriado extends Model
{
    use LogsActivity;

    protected $fillable = [
        'titulo',
        'fecha',
        'recurrente',
    ];

    protected $casts = [
        'fecha' => 'date',
        'recurrente' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('feriado')
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Feriado fue {$eventName}");
    }
}
