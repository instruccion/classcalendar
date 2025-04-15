<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    // Definimos los campos que son asignables en masa
    protected $fillable = [
        'user_id',
        'accion',
        'descripcion',
        'ip',
    ];

    // Indicamos que la tabla usa el timestamp de Laravel por defecto
    public $timestamps = true;

    // Relación con el usuario (quien ejecutó la acción)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
