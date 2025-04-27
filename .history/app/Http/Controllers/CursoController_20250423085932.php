<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CursoController extends Controller
{
    /**
     * Muestra la lista de cursos con filtros y scoping.
     */
    public function index(Request $request)
    {
        $usuario = auth()->user();
        $query = Curso::with('grupos');

        if ($request->filled('grupo_id')) {
            $query->whereHas('grupos', function ($q) use ($request) {
                $q->where('grupos.id', $request->grupo_id);
            });
        }

        if ($usuario->esAdministrador() && $request->filled('coordinacion_id')) {
            $grupoIds = Grupo::where('coordinacion_id', $request->coordinacion_id)->pluck('id');
            $query->whereHas('grupos', function ($q) use ($grupoIds) {
                $q->whereIn('grupos.id', $grupoIds);
            });
        }
        // Si es admin y NO tiene coordinación, ve todos
        if ($usuario->esAdministrador() && is_null($usuario->coordinacion_id)) {
            // no aplicar filtro adicional
        } else {
            // Si tiene coordinación, filtrar por ella
            $grupoIds = Grupo::where('coordinacion_id', $usuario->coordinacion_id)->pluck('id');
            $query->whereHas('grupos', function ($q) use ($grupoIds) {
                $q->whereIn('grupos.id', $grupoIds);
            });
        }

$cursos = $query->get();


        $cursos = $query->get();

        // Este es el listado para el filtro visible en la vista
        if ($usuario->esAdministrador() && is_null($usuario->coordinacion_id)) {
            $grupos = Grupo::with('coordinacion')->orderBy('nombre')->get();
        } else {
            $grupos = Grupo::with('coordinacion')
                ->where('coordinacion_id', $usuario->coordinacion_id)
                ->orderBy('nombre')->get();
        }

        // 👇 Este es el listado general para usar en el modal-editar.blade.php
        $gruposTodos = $usuario->esAdministrador() && is_null($usuario->coordinacion_id)
            ? Grupo::orderBy('nombre')->get()
            : Grupo::where('coordinacion_id', $usuario->coordinacion_id)->orderBy('nombre')->get();

        $coordinaciones = Coordinacion::orderBy('nombre')->get();

        return view('admin.cursos.index', compact(
            'cursos',
            'grupos',
            'coordinaciones',
            'usuario',
            'gruposTodos'
        ));
    }


    /**
     * Guarda un nuevo curso.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:inicial,recurrente,puntual',
            'duracion_horas' => 'required|integer|min:1',
            'grupo_ids' => 'required|array|min:1',
            'grupo_ids.*' => 'exists:grupos,id',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $curso = Curso::create([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'duracion_horas' => $request->duracion_horas,
            'descripcion' => $request->descripcion,
            'coordinacion_id' => auth()->user()->coordinacion_id, // Admin sin coordinación deja null
        ]);

        $curso->grupos()->attach($request->grupo_ids);

        return redirect()->route('admin.cursos.index')->with('success', 'Curso registrado correctamente.');
    }

    /**
     * Actualiza un curso existente.
     */
    public function update(Request $request, Curso $curso)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|string|in:inicial,recurrente,puntual',
            'descripcion' => 'nullable|string',
            'duracion_horas' => 'required|integer|min:1',
            'grupo_ids' => 'required|array|min:1',
            'grupo_ids.*' => 'required|exists:grupos,id',
        ]);

        $curso->update($validated);
        $curso->grupos()->sync($validated['grupo_ids']);

        return redirect()->route('admin.cursos.index')->with('success', 'Curso actualizado exitosamente.');
    }

    /**
     * Devuelve la vista de creación de cursos (no se usa con modal).
     */
    public function create()
    {
        $user = auth()->user();

        $grupos = $user->esAdministrador() && $user->coordinacion_id === null
            ? Grupo::with('coordinacion')->orderBy('nombre')->get()
            : Grupo::with('coordinacion')->where('coordinacion_id', $user->coordinacion_id)->orderBy('nombre')->get();

        return view('admin.cursos.create', compact('grupos'));
    }

    /**
     * Carga los datos para el modal de edición.
     */
    public function edit(Curso $curso)
    {
        $usuario = Auth::user();

        $grupo_ids = $curso->grupos()->pluck('grupos.id')->toArray();

        if ($usuario->esAdministrador() && is_null($usuario->coordinacion_id)) {
            $todos_grupos = Grupo::select('id', 'nombre')->orderBy('nombre')->get();
        } elseif ($usuario->coordinacion_id) {
            $todos_grupos = Grupo::where('coordinacion_id', $usuario->coordinacion_id)
                                 ->select('id', 'nombre')
                                 ->orderBy('nombre')
                                 ->get();
        } else {
            // En caso de que no sea admin ni tenga coordinación (por seguridad)
            $todos_grupos = collect(); // Retorna colección vacía
        }


        return response()->json([
            'id' => $curso->id,
            'nombre' => $curso->nombre,
            'tipo' => $curso->tipo,
            'descripcion' => $curso->descripcion,
            'duracion_horas' => $curso->duracion_horas,
            'grupo_ids' => $grupo_ids,
            'todos_grupos' => $todos_grupos
        ]);
    }

    /**
     * Elimina un curso.
     */
    public function destroy(Curso $curso)
    {
        $curso->delete();
        return redirect()->route('admin.cursos.index')->with('success', 'Curso eliminado correctamente.');
    }
}
