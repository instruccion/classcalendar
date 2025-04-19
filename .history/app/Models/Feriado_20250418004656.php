<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feriado extends Model
{
    use HasFactory;

    protected $table = 'feriados';

    protected $fillable = [
        'titulo',
        'fecha',
        'recurrente',
    ];

    protected $casts = [
        'fecha' => 'date',
        'recurrente' => 'boolean',
    ];
}
