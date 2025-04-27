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

            // Formatear correctamente fecha/hora
            $fechaInicio = $p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : null;
            $fechaFin = $p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : null;
            $horaInicio = $p->hora_inicio ? substr($p->hora_inicio, 0, 5) : null;
            $horaFin = $p->hora_fin ? substr($p->hora_fin, 0, 5) : null;

            $start = $fechaInicio;
            $end = $fechaFin;

            if ($start && $horaInicio && preg_match('/^\d{2}:\d{2}$/', $horaInicio)) {
                $start .= 'T' . $horaInicio;
            }

            if ($end && $horaFin && preg_match('/^\d{2}:\d{2}$/', $horaFin)) {
                $end .= 'T' . $horaFin;
            }

            return [
                'id' => $p->id,
                'title' => $cursoNombre,
                'start' => $start,
                'end' => $end,
                'color' => $coordinacionColor,
                'borderColor' => $coordinacionColor,
                'extendedProps' => [
                    'grupo' => $grupoNombre,
                    'aula' => $aulaNombre,
                    'hora_inicio' => $horaInicio ?? '',
                    'hora_fin' => $horaFin ?? '',
                    'estado' => ucfirst($estado),
                    'fecha_inicio_fmt' => $fechaInicio ? date('d/m/Y', strtotime($fechaInicio)) : '',
                    'fecha_fin_fmt' => $fechaFin ? date('d/m/Y', strtotime($fechaFin)) : '',
                    'estadoDisplay' => ucfirst($estado),
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
        return $this->index($request);
    }
}
