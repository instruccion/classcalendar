@component('mail::message')
# Invitación a Curso

Hola {{ $programacion->instructor->nombre ?? 'Instructor' }},

Se te ha asignado el siguiente curso:

- **Curso:** {{ $programacion->curso->nombre ?? 'Sin nombre' }}
- **Grupo:** {{ $programacion->grupo->nombre ?? 'Sin grupo' }}
- **Fecha Inicio:** {{ $programacion->fecha_inicio?->format('d/m/Y') ?? 'N/A' }}
- **Fecha Fin:** {{ $programacion->fecha_fin?->format('d/m/Y') ?? 'N/A' }}
- **Horario:**
  @if($programacion->hora_inicio && $programacion->hora_fin)
    {{ $programacion->hora_inicio instanceof \Carbon\Carbon ? $programacion->hora_inicio->format('H:i') : $programacion->hora_inicio }} -
    {{ $programacion->hora_fin instanceof \Carbon\Carbon ? $programacion->hora_fin->format('H:i') : $programacion->hora_fin }}
  @else
    N/A
  @endif
- **Lugar:** {{ $programacion->aula->ubicacion ?? 'No asignado' }}

@component('mail::button', ['url' => route('mi-agenda.confirmar', ['programacion' => $programacion->id])])
Confirmar Asistencia
@endcomponent

Gracias por tu compromiso.

@endcomponent
