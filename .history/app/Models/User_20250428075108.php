<?php

namespace App\Models;

// Importa la clase Coordinacion si no está ya importada
use App\Models\Coordinacion;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importa BelongsTo
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Asegúrate de que todos los 'use' necesarios estén presentes

class User extends Authenticatable // Puede que tu clase base sea diferente
{
    use HasFactory, Notifiable; // Asegúrate de que los traits necesarios estén presentes

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol', // Columna para el rol (ej: 'administrador', 'coordinador')
        'coordinacion_id',
        'is_active',
        // 'rol_id', // Si usaras IDs en lugar de strings para roles
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
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
     * Obtiene la coordinación a la que pertenece el usuario.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coordinacion(): BelongsTo
    {
        return $this->belongsTo(Coordinacion::class);
    }


    // --- MÉTODOS DE ROL ---
    public function esAdministrador(): bool {
        // Compara el valor de la columna 'rol'
        return $this->rol === 'administrador';
    }

    public function esCoordinador(): bool {
        return $this->rol === 'coordinador';
    }

    public function esAnalista(): bool {
        return $this->rol === 'analista';
    }

    public function esInstructor(): bool {
        return $this->rol === 'instructor';
    }

    public function instructor()
    {
        return $this->hasOne(\App\Models\Instructor::class, 'user_id');
    }




}
