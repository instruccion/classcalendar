<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Programacion;
use App\Models\Instructor;

class MiAgendaController extends Controller
{
    public function index(Request $request)
    {
        $instructorId = $request->query('instructor_id');

        if (!$instructorId) {
            return response()->json([]);
        }

        $programaciones = Programacion::with(['curso', 'grupo.coordinacion', 'aula'])
            ->where('instructor_id', $instructorId)
            ->orderBy('fecha_inicio')
            ->get();

        $eventos = $programaciones->map(function ($p) {
            $horaInicio = is_string($p->hora_inicio) ? substr($p->hora_inicio, 0, 5) : ($p->hora_inicio ? $p->hora_inicio->format('H:i') : '08:00');
            $horaFin = is_string($p->hora_fin) ? substr($p->hora_fin, 0, 5) : ($p->hora_fin ? $p->hora_fin->format('H:i') : '18:00');

            return [
                'title' => $p->curso->nombre ?? 'Curso sin título',
                'start' => ($p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : now()->format('Y-m-d')) . 'T' . $horaInicio,
                'end' => ($p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : now()->format('Y-m-d')) . 'T' . $horaFin,
                'color' => $p->grupo->coordinacion->color ?? '#3788D8',
                'extendedProps' => [
                    'grupo' => $p->grupo->nombre ?? '—',
                    'aula' => $p->aula->nombre ?? '—',
                    'estado' => ucfirst($p->estado_confirmacion ?? 'pendiente'),
                ],
            ];
        });

        return response()->json($eventos);
    }

    public function miAgenda()
    {
        $usuario = auth()->user();
        $instructor = $usuario->instructor;

        if (!$instructor) {
            abort(403, 'No tienes acceso a esta sección.');
        }

        $programaciones = Programacion::with(['curso', 'grupo'])
            ->where('instructor_id', $instructor->id)
            ->orderBy('fecha_inicio')
            ->get();

        $eventos = $programaciones->map(function ($p) {
            $horaInicio = is_string($p->hora_inicio) ? substr($p->hora_inicio, 0, 5) : ($p->hora_inicio ? $p->hora_inicio->format('H:i') : '08:00');
            $horaFin = is_string($p->hora_fin) ? substr($p->hora_fin, 0, 5) : ($p->hora_fin ? $p->hora_fin->format('H:i') : '18:00');

            return [
                'title' => $p->curso->nombre ?? 'Curso sin título',
                'start' => ($p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : now()->format('Y-m-d')) . 'T' . $horaInicio,
                'end' => ($p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : now()->format('Y-m-d')) . 'T' . $horaFin,
                'color' => '#3788D8',
            ];
        });

        return view('instructores.agenda', [
            'programaciones' => $programaciones,
            'eventosJson' => $eventos->toJson(),
        ]);
    }

    public function confirmarCurso(Programacion $programacion)
    {
        $usuario = auth()->user();
        $instructor = $usuario->instructor;

        if (!$instructor || $programacion->instructor_id !== $instructor->id) {
            abort(403, 'No tienes permiso para confirmar esta programación.');
        }

        $programacion->estado_confirmacion = 'confirmado';
        $programacion->fecha_confirmacion = now();
        $programacion->save();

        return redirect()->route('instructores.agenda')->with('success', 'Has confirmado tu asistencia al curso.');
    }
}
