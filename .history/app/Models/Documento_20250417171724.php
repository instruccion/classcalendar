<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = ['nombre', 'es_obligatorio'];

    public function instructores()
    {
        return $this->belongsToMany(Instructor::class, 'documento_instructor')
            ->withPivot('fecha_vencimiento')
            ->withTimestamps();
    }
}
