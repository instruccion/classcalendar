<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Programacion;
use App\Models\Instructor;
use App\Models\Coordinacion;

class MiAgendaController extends Controller
{
    public function index(Request $request)
    {
        $instructorId = $request->query('instructor_id');

        // 🔥 Si no viene en el request, usar el instructor del usuario logueado
        if (!$instructorId && auth()->user()?->instructor) {
            $instructorId = auth()->user()->instructor->id;
        }

        if (!$instructorId) {
            return response()->json([]);
        }

        $programaciones = Programacion::with([
                'curso',
                'grupo.coordinacion',
                'aula'
            ])
            ->where('instructor_id', $instructorId)
            ->orderBy('fecha_inicio')
            ->get();

        $eventos = $programaciones->map(function ($p) {
            $cursoNombre = $p->curso->nombre ?? 'Curso no especificado';
            $grupoNombre = $p->grupo->nombre ?? 'Grupo no especificado';
            $coordinacionColor = $p->grupo?->coordinacion?->color ?? '#3788D8';
            $aulaNombre = $p->aula->nombre ?? 'Aula no especificada';
            $estado = $p->estado_confirmacion ?? 'pendiente';

            $horaInicio = is_string($p->hora_inicio) ? substr($p->hora_inicio, 0, 5) : ($p->hora_inicio ? $p->hora_inicio->format('H:i') : '08:00');
            $horaFin = is_string($p->hora_fin) ? substr($p->hora_fin, 0, 5) : ($p->hora_fin ? $p->hora_fin->format('H:i') : '18:00');

            return [
                'title' => $cursoNombre,
                'start' => $p->fecha_inicio?->format('Y-m-d') . 'T' . $horaInicio,
                'end' => $p->fecha_fin?->format('Y-m-d') . 'T' . $horaFin,
                'color' => $coordinacionColor,
                'borderColor' => $coordinacionColor,
                'extendedProps' => [
                    'grupo' => $grupoNombre,
                    'aula' => $aulaNombre,
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'estado' => $estado,
                    'estadoDisplay' => ucfirst($estado),
                    'fecha_inicio_fmt' => $p->fecha_inicio?->format('d/m/Y') ?? 'N/A',
                    'fecha_fin_fmt' => $p->fecha_fin?->format('d/m/Y') ?? 'N/A',
                ]
            ];
        });

        return response()->json($eventos);
    }



    public function agendaAdministrador(Request $request)
    {
        $instructor_id = $request->get('instructor_id');
        $instructores = Instructor::orderBy('nombre')->get();
        $programaciones = collect();
        $selectedInstructor = null;

        if ($instructor_id) {
            $selectedInstructor = Instructor::find($instructor_id);
            $programaciones = Programacion::where('instructor_id', $instructor_id)
                ->with(['curso', 'grupo', 'aula'])
                ->orderBy('fecha_inicio')
                ->get();
        }

        return view('admin.instructores.agenda', compact('instructores', 'programaciones', 'instructor_id', 'selectedInstructor'));
    }

    public function apiAgendaInstructor(Request $request)
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
            $horaInicio = is_string($p->hora_inicio) ? substr($p->hora_inicio, 0, 5) : ($p->hora_inicio ? $p->hora_inicio->format('H:i') : '00:00');
            $horaFin = is_string($p->hora_fin) ? substr($p->hora_fin, 0, 5) : ($p->hora_fin ? $p->hora_fin->format('H:i') : '23:59');

            return [
                'title' => $p->curso->nombre ?? 'Curso sin título',
                'start' => ($p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : '2025-01-01') . 'T' . $horaInicio,
                'end' => ($p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : '2025-01-01') . 'T' . $horaFin,
                'color' => $p->grupo->coordinacion->color ?? '#3788D8',
                'extendedProps' => [
                    'grupo' => $p->grupo->nombre ?? '—',
                    'aula' => $p->aula->nombre ?? '—',
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'estado' => ucfirst($p->estado_confirmacion ?? 'pendiente'),
                    'estadoDisplay' => ucfirst($p->estado_confirmacion ?? 'pendiente'),
                    'fecha_inicio_fmt' => $p->fecha_inicio ? $p->fecha_inicio->format('d/m/Y') : '—',
                    'fecha_fin_fmt' => $p->fecha_fin ? $p->fecha_fin->format('d/m/Y') : '—',
                ],
            ];
        });

        return response()->json($eventos);
    }

    public function confirmarDesdeCorreo(Programacion $programacion)
    {
        // Verificamos si el usuario actual está asociado como instructor
        $instructor = $programacion->instructor;

        if (!$instructor || !$instructor->user_id || auth()->id() !== $instructor->user_id) {
            abort(403, 'No tienes permiso para confirmar esta programación.');
        }

        // Actualizamos el estado de confirmación
        $programacion->estado_confirmacion = 'confirmado';
        $programacion->fecha_confirmacion = now();
        $programacion->save();

        return redirect()->route('instructores.agenda')->with('success', 'Has confirmado tu asistencia al curso.');
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
                'start' => ($p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : '2025-01-01') . 'T' . $horaInicio,
                'end' => ($p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : '2025-01-01') . 'T' . $horaFin,
                'color' => '#3788D8',
            ];
        });

        // Aquí garantizamos que $eventosJson sea siempre JSON válido
        $eventosJson = $eventos->isEmpty() ? '[]' : $eventos->toJson();

        return view('instructores.agenda', [
            'programaciones' => $programaciones,
            'eventosJson' => $eventosJson,
            'instructor' => $instructor, // Para mostrar el nombre
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
