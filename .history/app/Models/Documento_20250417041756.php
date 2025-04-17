<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    public function instructores()
    {
        return $this->belongsToMany(Instructor::class, 'documento_instructor')
            ->withPivot('fecha_vencimiento')
            ->withTimestamps();
    }

}
