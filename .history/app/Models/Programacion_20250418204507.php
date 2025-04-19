<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon; // Necesario para manejar fechas y horas

class Programacion extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     * Laravel intentaría buscar 'programacions' por defecto,
     * pero tu tabla se llama 'programaciones'.
     * @var string
     */
    protected $table = 'programaciones';

    /**
     * Los atributos que se pueden asignar masivamente.
     * Lista aquí todas las columnas de tu tabla 'programaciones'
     * que quieres poder llenar desde un formulario.
     * @var array<int, string>
     */
    protected $fillable = [
        'curso_id',
        'grupo_id',
        'instructor_id',
        'aula_id',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'notificado_inac',
        'fecha_notificacion_inac',
        'estado',
        'bloque_codigo',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * Esto ayuda a Laravel a manejar fechas, booleanos, etc. correctamente.
     * @var array<string, string>
     */
    protected $casts = [
        // Convierte estas columnas a objetos Carbon (fecha y hora)
        // 'datetime:Y-m-d' indica que solo nos importa la parte de la fecha
        'fecha_inicio' => 'datetime:Y-m-d',
        'fecha_fin' => 'datetime:Y-m-d',
        'fecha_notificacion_inac' => 'date', // 'date' es solo fecha

        // Para las horas, 'datetime:H:i' las trata como hora dentro de un objeto Carbon
        // Esto puede ser útil para cálculos, pero a veces manejarlas como string es más simple.
        // Si tienes problemas, podrías quitar estos dos casts y manejarlas como string.
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',

        // Convierte notificado_inac (que es tinyint(1)) a true/false
        'notificado_inac' => 'boolean',

        // 'estado' es un ENUM. No necesita cast por defecto, se tratará como string.
        // Si usaras Enums de PHP 8.1+, el cast sería diferente:
        // 'estado' => \App\Enums\EstadoProgramacion::class,
    ];

    // -------------------------------------------------------------------------
    // RELACIONES CON OTROS MODELOS
    // Esto le dice a Laravel cómo esta Programacion se conecta con otras tablas.
    // Los nombres de los métodos (curso, grupo, etc.) son importantes.
    // -------------------------------------------------------------------------

    /**
     * Obtiene el Curso asociado a esta programación.
     * Una Programación PERTENECE A (belongsTo) un Curso.
     */
    public function curso(): BelongsTo
    {
        // Busca en la tabla 'cursos' usando la columna 'curso_id' de esta tabla.
        return $this->belongsTo(Curso::class);
    }

    /**
     * Obtiene el Grupo asociado a esta programación.
     * Una Programación PERTENECE A (belongsTo) un Grupo.
     */
    public function grupo(): BelongsTo
    {
        // Busca en la tabla 'grupos' usando la columna 'grupo_id'.
        return $this->belongsTo(Grupo::class);
    }

    /**
     * Obtiene el Instructor asociado a esta programación.
     * Una Programación PERTENECE A (belongsTo) un Instructor.
     */
    public function instructor(): BelongsTo
    {
         // Busca en la tabla 'instructores' usando la columna 'instructor_id'.
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Obtiene el Aula asociada a esta programación.
     * Una Programación PERTENECE A (belongsTo) un Aula.
     */
    public function aula(): BelongsTo
    {
        // Busca en la tabla 'aulas' usando la columna 'aula_id'.
        return $this->belongsTo(Aula::class);
    }

    /**
     * (Opcional) Obtiene el Usuario que creó esta programación.
     * Necesitarías añadir una columna 'user_id' a tu tabla 'programaciones'.
     */
    // public function programador(): BelongsTo
    // {
    //     // Busca en la tabla 'users' usando la columna 'user_id'.
    //     return $this->belongsTo(User::class, 'user_id');
    // }

    /**
      * (Opcional) Obtiene la Coordinación a la que pertenece esta programación.
      * Necesitarías añadir una columna 'coordinacion_id' a tu tabla 'programaciones'.
      */
    // public function coordinacion(): BelongsTo
    // {
    //      // Busca en la tabla 'coordinaciones' usando la columna 'coordinacion_id'.
    //      return $this->belongsTo(Coordinacion::class);
    // }

    // -------------------------------------------------------------------------
    // (Opcional) Accessors & Mutators - Para manipular datos al leer/guardar
    // -------------------------------------------------------------------------

    /**
      * (Ejemplo Avanzado) Combina fecha_inicio y hora_inicio en un solo objeto Carbon.
      * Podrías acceder a él como $programacion->inicio_completo
      * Útil para comparar rangos de tiempo completos.
      */
    public function getInicioCompletoAttribute(): ?Carbon
    {
        if ($this->fecha_inicio && $this->hora_inicio) {
            try {
                // Carbon necesita una fecha válida para interpretar la hora. Usamos la fecha_inicio.
                 return Carbon::parse($this->fecha_inicio->format('Y-m-d') . ' ' . $this->hora_inicio->format('H:i:s'));
            } catch (\Exception $e) {
                return null; // Devuelve null si hay error de formato
            }
        }
        return null;
    }

    /**
      * (Ejemplo Avanzado) Combina fecha_fin y hora_fin en un solo objeto Carbon.
      * Podrías acceder a él como $programacion->fin_completo
      */
    public function getFinCompletoAttribute(): ?Carbon
    {
         if ($this->fecha_fin && $this->hora_fin) {
             try {
                 return Carbon::parse($this->fecha_fin->format('Y-m-d') . ' ' . $this->hora_fin->format('H:i:s'));
             } catch (\Exception $e) {
                 return null;
             }
         }
         return null;
    }

}
