@php
    $instructor = $programacion->instructor;
    $curso = $programacion->curso;
    $grupo = $programacion->grupo;
@endphp

<h2>¡Hola {{ $instructor->nombre }}!</h2>

<p>Has sido asignado para dictar el siguiente curso:</p>

<ul>
    <li><strong>Curso:</strong> {{ $curso->nombre }}</li>
    <li><strong>Grupo:</strong> {{ $grupo->nombre }}</li>
    <li><strong>Fecha Inicio:</strong> {{ $programacion->fecha_inicio->format('d/m/Y') }}</li>
    <li><strong>Hora Inicio:</strong> {{ $programacion->hora_inicio }}</li>
    <li><strong>Duración:</strong> {{ $curso->duracion_horas }} horas</li>
</ul>

<p>Por favor, confirma tu participación:</p>

<a href="{{ route('instructor.confirmar', ['programacion' => $programacion->id, 'token' => $programacion->token_confirmacion]) }}"
   style="background-color: #16a34a; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">
    ✅ Confirmar Participación
</a>

<p>Si no puedes participar, puedes rechazarla desde tu agenda.</p>

<p>Gracias.</p>
