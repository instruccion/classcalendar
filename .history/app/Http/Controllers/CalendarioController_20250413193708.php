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
}
