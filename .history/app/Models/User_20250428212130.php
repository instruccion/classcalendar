<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol', // Ejemplo: administrador, coordinador, analista, instructor
        'coordinacion_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relación: el usuario pertenece a una coordinación (opcional).
     */
    public function coordinacion(): BelongsTo
    {
        return $this->belongsTo(Coordinacion::class);
    }

    /**
     * Relación: el usuario TIENE UN instructor asociado.
     */
    public function instructor()
    {
        return $this->hasOne(\App\Models\Instructor::class, 'user_id');
    }

    // --- MÉTODOS DE ROL ---

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }

    public function esCoordinador(): bool
    {
        return $this->rol === 'coordinador';
    }

    public function esAnalista(): bool
    {
        return $this->rol === 'analista';
    }

    public function esInstructor(): bool
    {
        return $this->rol === 'instructor';
    }
}
