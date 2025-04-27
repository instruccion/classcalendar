<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Programacion;

class MiAgendaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $programaciones = Programacion::with(['curso', 'grupo', 'aula'])
            ->where('instructor_id', $user->id)
            ->orderBy('fecha_inicio')
            ->get();

        $eventos = $programaciones->map(function ($p) {
            return [
                'id' => $p->id,
                'titulo' => $p->curso->nombre ?? 'Curso sin título',
                'inicio' => $p->fecha_inicio . 'T' . $p->hora_inicio,
                'fin' => $p->fecha_fin . 'T' . $p->hora_fin,
                'color' => $p->estado_confirmacion === 'confirmado' ? '#22c55e' : ($p->estado_confirmacion === 'rechazado' ? '#ef4444' : '#3b82f6'),
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
}
