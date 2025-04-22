<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class GrupoController extends Controller
{
    // Método para mostrar grupos filtrados por coordinación
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->rol === 'administrador') {
            $coordinacionId = $request->get('coordinacion_id');
        } else {
            $coordinacionId = $user->coordinacion_id;
        }

        $grupos = Grupo::with('coordinacion')
            ->when($coordinacionId, fn($q) => $q->where('coordinacion_id', $coordinacionId))
            ->orderBy('nombre')
            ->get();

        $coordinaciones = Coordinacion::orderBy('nombre')->get();

        return view('admin.grupos.index', compact('grupos', 'coordinaciones'));
    }



    // Método para obtener los grupos por coordinación para solicitudes AJAX
    public function getGruposByCoordinacionJson($coordinacion_id): JsonResponse
    {
        $user = Auth::user();

        if ($user->rol !== 'administrador' && $user->coordinacion_id != $coordinacion_id) {
            return response()->json(['message' => 'Acceso no autorizado a esta coordinación.'], 403);
        }

        $grupos = Grupo::where('coordinacion_id', $coordinacion_id)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return response()->json($grupos);
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

            return response()->json($grupos);

    }

    public function update(Request $request, Grupo $grupo)
    {
        $user = Auth::user();

        // Validación básica
        $validated = $request->validate([
            'nombre' => 'required|string|max:191',
            'descripcion' => 'nullable|string',
            'coordinacion_id' => 'nullable|exists:coordinaciones,id',
        ]);

        // Si NO es administrador, restringimos
        if ($user->rol !== 'administrador') {
            // Solo puede editar grupos de su coordinación
            if ($grupo->coordinacion_id !== $user->coordinacion_id) {
                abort(403, 'No tienes permiso para modificar este grupo.');
            }

            // Forzar coordinación (evitar spoofing)
            $validated['coordinacion_id'] = $user->coordinacion_id;
        }

        // Actualizar grupo
        $grupo->update($validated);

        return redirect()->route('admin.grupos.index')
            ->with('success', 'Grupo actualizado correctamente.');
    }


    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nombre' => 'required|string|max:191',
            'descripcion' => 'nullable|string',
        ]);

        if ($user->rol !== 'administrador') {
            $validated['coordinacion_id'] = $user->coordinacion_id;
        } else {
            $request->validate([
                'coordinacion_id' => 'required|exists:coordinaciones,id'
            ]);
            $validated['coordinacion_id'] = $request->coordinacion_id;
        }

        Grupo::create($validated);

        return redirect()->route('admin.grupos.index')->with('success', 'Grupo registrado correctamente.');
    }

    public function destroy(Grupo $grupo)
    {
        // Verifica si el grupo tiene cursos asignados y evita borrado si es necesario
        if ($grupo->cursos()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar el grupo porque tiene cursos asignados.');
        }

        $grupo->delete();

        return redirect()->route('admin.grupos.index')->with('success', 'Grupo eliminado correctamente.');
    }

    \App\Models\GrupoCurso::create([
        'grupo_id' => $grupo->id,
        'curso_id' => $cursoId,
    ]);


}
