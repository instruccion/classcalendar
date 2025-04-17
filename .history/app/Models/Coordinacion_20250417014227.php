<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Coordinacion extends Model
{
    use LogsActivity;

    protected $table = 'coordinaciones';

    protected $fillable = ['nombre', 'descripcion', 'color', 'activa'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('coordinacion')
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Coordinación fue {$eventName}");
    }

    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }
}
