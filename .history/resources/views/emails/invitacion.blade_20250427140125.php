@component('mail::message')
# Invitación a Curso

Hola {{ $programacion->instructor->nombre }},

Se te ha asignado el siguiente curso:

- **Curso:** {{ $programacion->curso->nombre ?? 'Sin nombre' }}
- **Grupo:** {{ $programacion->grupo->nombre ?? 'Sin grupo' }}
- **Fecha Inicio:** {{ $programacion->fecha_inicio?->format('d/m/Y') ?? 'N/A' }}
- **Fecha Fin:** {{ $programacion->fecha_fin?->format('d/m/Y') ?? 'N/A' }}
- **Horario:** {{ $programacion->hora_inicio?->format('H:i') ?? 'N/A' }} - {{ $programacion->hora_fin?->format('H:i') ?? 'N/A' }}
- **Lugar:** {{ $programacion->aula->ubicacion ?? 'Sin ubicación' }}

@component('mail::button', ['url' => route('mi-agenda.confirmar', ['programacion' => $programacion->id])])
Confirmar Asistencia
@endcomponent

Gracias por tu compromiso.

@endcomponent
