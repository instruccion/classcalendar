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

    // --- ¡AÑADE ESTE MÉTODO! ---
    /**
     * Obtiene la coordinación a la que pertenece el usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coordinacion(): BelongsTo
    {
        // Asume que la columna de clave foránea en la tabla 'users' es 'coordinacion_id'
        // Si tu columna se llama diferente, pásala como segundo argumento:
        // return $this->belongsTo(Coordinacion::class, 'tu_columna_fk');
        return $this->belongsTo(Coordinacion::class);
    }
    // --- FIN DEL MÉTODO A AÑADIR ---

    // Puedes tener otros métodos/relaciones aquí...
}
