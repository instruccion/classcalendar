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

            // 👉 Importante: hora_inicio y hora_fin correctamente manejadas
            $horaInicio = optional($p->hora_inicio)->format('H:i') ?? '00:00';
            $horaFin = optional($p->hora_fin)->format('H:i') ?? '23:59';

            $start = $p->fecha_inicio?->format('Y-m-d') . 'T' . $horaInicio;
            $end = $p->fecha_fin?->format('Y-m-d') . 'T' . $horaFin;

            $estadoDisplay = ucfirst($estado);

            return [
                'id'            => $p->id,
                'title'         => $cursoNombre,
                'start'         => $start,
                'end'           => $end,
                'color'         => $coordinacionColor,
                'borderColor'   => $coordinacionColor,
                'extendedProps' => [
                    'grupo'       => $grupoNombre,
                    'aula'        => $aulaNombre,
                    'hora_inicio' => $horaInicio,
                    'hora_fin'    => $horaFin,
                    'estado'      => $estado,
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
            return response()->json([]);
        }

        $programaciones = Programacion::with(['curso', 'grupo.coordinacion', 'aula'])
            ->where('instructor_id', $instructorId)
            ->orderBy('fecha_inicio')
            ->get();

        $eventos = $programaciones->map(function ($p) {
            $horaInicio = optional($p->hora_inicio)->format('H:i') ?? '00:00';
            $horaFin = optional($p->hora_fin)->format('H:i') ?? '23:59';

            return [
                'title' => $p->curso->nombre ?? 'Curso sin título',
                'start' => $p->fecha_inicio?->format('Y-m-d') . 'T' . $horaInicio,
                'end' => $p->fecha_fin?->format('Y-m-d') . 'T' . $horaFin,
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
