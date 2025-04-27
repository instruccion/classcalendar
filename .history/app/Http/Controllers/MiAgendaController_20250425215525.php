<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Programacion;
use App\Models\Instructor;

class MiAgendaController extends Controller
{
    public function index(Request $request)
    {
        $instructorId = $request->get('instructor_id') ?? Auth::id();

        $programaciones = Programacion::with(['curso', 'grupo.coordinacion', 'aula'])
            ->where('instructor_id', $instructorId)
            ->orderBy('fecha_inicio')
            ->get();

        $eventos = $programaciones->map(function ($p) {
            return [
                'titulo' => $p->curso->nombre ?? 'Curso sin título',
                'start' => $p->fecha_inicio . 'T' . $p->hora_inicio,
                'end' => $p->fecha_fin . 'T' . $p->hora_fin,
                'color' => $p->grupo->coordinacion->color ?? '#2563EB',
                'extendedProps' => [
                    'grupo' => $p->grupo->nombre ?? '—',
                    'aula' => $p->aula->nombre ?? '—',
                    'hora_inicio' => $p->hora_inicio,
                    'hora_fin' => $p->hora_fin,
                    'estado' => $p->estado_confirmacion ?? 'pendiente',
                ]
            ];
        });

        return response()->json($eventos);
    }



    public function agendaInstructor(Instructor $instructor)
    {
        return view('admin.instructores.agenda', compact('instructor'));
    }

    public function agendaAdministrador(Request $request)
    {
        $instructor_id = $request->get('instructor_id');
        $instructores = Instructor::orderBy('nombre')->get();
        $programaciones = collect();

        if ($instructor_id) {
            $programaciones = Programacion::where('instructor_id', $instructor_id)
                ->with(['curso', 'grupo', 'aula'])
                ->orderBy('fecha_inicio')
                ->get();
        }

        return view('admin.instructores.agenda', compact('instructores', 'programaciones'));
    }
}
