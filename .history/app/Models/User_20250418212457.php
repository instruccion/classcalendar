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
    // ¡IMPORTANTE! Añade el trait LogsActivity si lo necesitas de tu versión original
    // use HasFactory, Notifiable, \Spatie\Activitylog\Traits\LogsActivity;
    use HasFactory, Notifiable; // Si no usas LogsActivity aquí

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol', // Asegúrate de que 'rol' esté en fillable
        'coordinacion_id', // Asegúrate de que 'coordinacion_id' esté en fillable
        'is_active', // Asegúrate de que 'is_active' esté en fillable
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean', // Añade el cast para is_active
        ];
    }

    /**
     * Obtiene la coordinación a la que pertenece el usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coordinacion(): BelongsTo
    {
        return $this->belongsTo(Coordinacion::class);
    }

    // --- MÉTODOS DE ROL RESTAURADOS ---
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
    // --- FIN DE MÉTODOS DE ROL ---

    // --- MÉTODO PARA LOGS DE SPATIE (Si lo usas) ---
    // Si usas Spatie Activity Log, restaura este método también
    // public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    // {
    //     return \Spatie\Activitylog\LogOptions::defaults()
    //         ->useLogName('usuario')
    //         ->logFillable() // Loguea todos los fillable
    //         ->logOnlyDirty()
    //         ->dontSubmitEmptyLogs();
    // }
    // --- FIN DE MÉTODO SPATIE ---

}
