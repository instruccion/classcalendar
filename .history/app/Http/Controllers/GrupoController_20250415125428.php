<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class GrupoController extends Controller
{
    // Método para mostrar grupos filtrados por coordinación
    public function index(Request $request)
    {
        $usuario = Auth::user();
        // Si es admin, puede ver todos los grupos, si no, solo los de su coordinación
        $coordinacionId = $usuario->rol === 'administrador' ? null : $usuario->coordinacion_id;

        $grupos = Grupo::with('coordinacion')
            ->when($coordinacionId, fn($q) => $q->where('coordinacion_id', $coordinacionId))
            ->orderBy('nombre')
            ->get();

        $coordinaciones = Coordinacion::orderBy('nombre')->get();

        return view('admin.grupos.index', compact('grupos', 'coordinaciones', 'usuario', 'coordinacionId'));
    }

    // Método para obtener los grupos por coordinación para solicitudes AJAX
    public function getGruposByCoordinacionJson(Request $request, Coordinacion $coordinacion): JsonResponse
    {
        $user = Auth::user();

        // Seguridad: Si no es admin y la coordinación solicitada no es la suya, denegar
        if ($user->rol !== 'administrador' && $user->coordinacion_id != $coordinacion->id) {
            return response()->json(['message' => 'Acceso no autorizado a esta coordinación.'], 403);
        }

        $grupos = $coordinacion->grupos()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return response()->json(['grupos' => $grupos]);
    }

    // Método para obtener los grupos visibles para el usuario actual
    public function getGruposVisiblesPorUsuarioJson(): JsonResponse
    {
        $user = Auth::user();
        $query = Grupo::query()->select('id', 'nombre')->orderBy('nombre');

        if ($user->rol !== 'administrador') {
            if ($user->coordinacion_id) {
                // Filtrar por la coordinación del usuario NO admin
                $query->where('coordinacion_id', $user->coordinacion_id);
            } else {
                // Si no tiene coordinación, no puede ver grupos
                return response()->json(['grupos' => []]);
            }
        }

        // Si es admin, no se aplica filtro aquí
        $grupos = $query->get();
        return response()->json(['grupos' => $grupos]);
    }

    // Método para obtener los grupos por coordinación (basado en request)
    public function gruposPorCoordinacion(Request $request): JsonResponse
    {
        $coordinacionId = $request->get('coordinacion_id');

        if (!$coordinacionId) {
            return response()->json(['grupos' => []]);
        }

        $grupos = Grupo::where('coordinacion_id', $coordinacionId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json(['grupos' => $grupos]);
    }

    
}
