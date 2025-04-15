<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class GrupoController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $coordinacionId = $usuario->rol === 'administrador' ? null : $usuario->coordinacion_id;

        $grupos = Grupo::with('coordinacion')
            ->when($coordinacionId, fn($q) => $q->where('coordinacion_id', $coordinacionId))
            ->orderBy('nombre')
            ->get();

        $coordinaciones = Coordinacion::orderBy('nombre')->get();

        return view('admin.grupos.index', compact('grupos', 'coordinaciones', 'usuario', 'coordinacionId'));
    }

    /**
     * Devuelve los grupos disponibles según la coordinación, usando un solo método para AJAX.
     * Si no se especifica la coordinación, devuelve todos los visibles para el usuario.
     */
    public function getGrupos(Request $request): JsonResponse
    {
        $user = Auth::user();
        $coordinacionId = $request->get('coordinacion_id');

        if ($user->rol !== 'administrador') {
            if ($user->coordinacion_id === null) {
                return response()->json(['grupos' => []]);
            }
            // Si es no-admin, fuerza su coordinación
            $coordinacionId = $user->coordinacion_id;
        }

        $query = Grupo::select('id', 'nombre')->orderBy('nombre');

        if ($coordinacionId) {
            $query->where('coordinacion_id', $coordinacionId);
        }

        $grupos = $query->get();

        return response()->json(['grupos' => $grupos]);
    }

    // store(), edit(), update(), destroy() los mantienes como los tenías antes.
}
