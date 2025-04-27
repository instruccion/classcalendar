<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Programacion;
use App\Models\Instructor;
use App\Models\Coordinacion;

class MiAgendaController extends Controller
{
    public function index(Request $request)
    {
        $instructorId = $request->get('instructor_id');

        if (!$instructorId) {
            return response()->json([]);
        }

        $programaciones = Programacion::with([
                'curso',
                'grupo.coordinacion' => function ($query) {
                    $query->select('id', 'color');
                },
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

            // ✅ Corrección real aquí:
            $horaInicio = $p->hora_inicio ? substr($p->hora_inicio, 0, 5) : '00:00';
            $horaFin = $p->hora_fin ? substr($p->hora_fin, 0, 5) : '23:59';

            $start = $p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : null;
            $end = $p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : null;

            $horaInicio = $p->hora_inicio ? substr($p->hora_inicio, 0, 5) : null;
            $horaFin = $p->hora_fin ? substr($p->hora_fin, 0, 5) : null;

            if ($start && $horaInicio) {
                $start .= 'T' . $horaInicio;
            }
            if ($end && $horaFin) {
                $end .= 'T' . $horaFin;
            }

            $estadoDisplay = ucfirst($estado);

            return [
                'id' => $p->id,
                'title' => $cursoNombre,
                'start' => $start, // ahora bien armado
                'end' => $end,     // ahora bien armado
                'color' => $coordinacionColor,
                'borderColor' => $coordinacionColor,
                'extendedProps' => [
                    'grupo' => $grupoNombre,
                    'aula' => $aulaNombre,
                    'hora_inicio' => $horaInicio ?? 'N/A',
                    'hora_fin' => $horaFin ?? 'N/A',
                    'estado' => $estado,
                    'estadoDisplay' => $estadoDisplay,
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
            return response()->json([]); // Si no se pasa el instructor, devolver vacío
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
            // ✅ Corrección aquí también:
            $horaInicio = $p->hora_inicio ? substr($p->hora_inicio, 0, 5) : '00:00';
            $horaFin = $p->hora_fin ? substr($p->hora_fin, 0, 5) : '23:59';

            return [
                'title' => $p->curso->nombre ?? 'Curso sin título',
                'start' => $p->fecha_inicio->format('Y-m-d') . 'T' . $horaInicio,
                'end' => $p->fecha_fin->format('Y-m-d') . 'T' . $horaFin,
                'color' => $p->grupo->coordinacion->color ?? '#3788D8',
                'extendedProps' => [
                    'grupo' => $p->grupo->nombre ?? '—',
                    'aula' => $p->aula->nombre ?? '—',
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'estado' => ucfirst($p->estado_confirmacion ?? 'pendiente')
                ]
            ];
        });

        return response()->json($eventos);
    }
}
