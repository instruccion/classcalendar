<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grupo extends Model
{
    protected $table = 'grupos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'coordinacion_id',
    ];

    public function coordinacion(): BelongsTo
    {
        return $this->belongsTo(Coordinacion::class);
    }
}
