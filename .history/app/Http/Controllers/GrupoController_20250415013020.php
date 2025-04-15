<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder; // Necesario para when/whereHas
use Illuminate\Http\JsonResponse; // Tipo de retorno explícito

class GrupoController extends Controller
{
    // ... index, store, edit, update, destroy ... (Mantenlos como estaban)

    /**
     * Devuelve grupos filtrados por coordinación para llamadas AJAX.
     * Renombrado para claridad.
     */
    public function getGruposByCoordinacionJson(Request $request, Coordinacion $coordinacion): JsonResponse // Usa Route Model Binding si la ruta incluye {coordinacion}
    {
         // Alternativa si pasas ID como query param:
         // $coordinacionId = $request->query('coordinacion_id');
         // if (!$coordinacionId) { return response()->json(['grupos' => []]); }
         // $coordinacion = Coordinacion::find($coordinacionId);
         // if (!$coordinacion) { return response()->json(['message' => 'Coordinación no encontrada'], 404); }

        $user = Auth::user();

        // Seguridad: Si no es admin Y la coordinación solicitada NO es la suya, denegar.
        if ($user->rol !== 'administrador' && $user->coordinacion_id != $coordinacion->id) {
             return response()->json(['message' => 'Acceso no autorizado a esta coordinación.'], 403);
        }

        $grupos = $coordinacion->grupos()
                          ->select('id', 'nombre') // Solo campos necesarios
                          ->orderBy('nombre')
                          ->get();

        return response()->json(['grupos' => $grupos]);
    }

    /**
     * Devuelve todos los grupos visibles por el usuario actual para llamadas AJAX.
     * (Admin ve todos, no-admin solo los de su coordinación).
     */
    public function getGruposVisiblesPorUsuarioJson(): JsonResponse
    {
        $user = Auth::user();
        $query = Grupo::query()->select('id', 'nombre')->orderBy('nombre');

        if ($user->rol !== 'administrador') {
            // Filtrar por la coordinación del usuario NO admin
            if ($user->coordinacion_id) {
                $query->where('coordinacion_id', $user->coordinacion_id);
            } else {
                // Si no tiene coordinación, no puede ver grupos
                return response()->json(['grupos' => []]); // Devuelve vacío directamente
            }
        }
        // Si es admin, no se aplica filtro aquí

        $grupos = $query->get();
        return response()->json(['grupos' => $grupos]);
    }
}
