<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarioController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();

        $requiereCambio = DB::table('users')
            ->where('id', $usuario->id)
            ->value('requiere_cambio');

        $coordinaciones = [];
        if ($usuario->rol === 'administrador') {
            $coordinaciones = DB::table('coordinaciones')->orderBy('nombre')->get();
        }

        $grupos = DB::table('grupos')->orderBy('nombre')->get();

        return view('admin.calendario.index', [
            'requiereCambio' => $requiereCambio,
            'coordinaciones' => $coordinaciones,
            'grupos' => $grupos,
            'usuario' => $usuario,
            'coordinacionId' => $usuario->coordinacion_id ?? null,
        ]);
    }

    public function eventos(Request $request)
    {
        $usuario = Auth::user();
        $grupoId = $request->query('grupo');
        $coordinacionId = $request->query('coordinacion');

        $query = DB::table('programaciones as cp')
            ->join('cursos as c', 'c.id', '=', 'cp.curso_id')
            ->leftJoin('coordinaciones as co', 'co.id', '=', 'c.coordinacion_id')
            ->select(
                'cp.id',
                'c.nombre as title',
                'cp.fecha_inicio as start',
                'cp.fecha_fin',
                'co.color'
            );

        if ($grupoId) {
            $query->where('cp.grupo_id', $grupoId);
        }

        if ($usuario->rol === 'administrador' && $coordinacionId) {
            $query->where('cp.coordinacion_id', $coordinacionId);
        } elseif ($usuario->rol !== 'administrador') {
            $query->where('cp.coordinacion_id', $usuario->coordinacion_id);
        }

        $eventos = $query->get()->map(function ($evento) {
            return [
                'id' => $evento->id,
                'title' => $evento->title,
                'start' => $evento->start,
                'end' => $evento->fecha_fin,
                'allDay' => true,
                'backgroundColor' => $evento->color ?? '#999999',
                'borderColor' => $evento->color ?? '#999999',
            ];
        });


        return response()->json($eventos);
    }

}
