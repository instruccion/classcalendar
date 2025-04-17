<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'coordinacion_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('usuario')
            ->logOnly(['name', 'email', 'rol', 'coordinacion_id', 'is_active']) // evita auditar password
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Usuario fue {$eventName}");
    }

    // Métodos de rol
    public function esAdministrador(): bool {
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
}
