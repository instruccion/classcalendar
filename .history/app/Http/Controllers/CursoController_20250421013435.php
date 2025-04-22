<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class CursoController extends Controller
{
    /**
     * Muestra la lista de cursos con filtros y scoping.
     */
    public function index(Request $request)
    {
        $usuario = auth()->user();

        $query = Curso::with('grupos');

        // Filtrado por grupo si se selecciona uno
        if ($request->filled('grupo_id')) {
            $query->whereHas('grupos', function ($q) use ($request) {
                $q->where('grupos.id', $request->grupo_id);
            });
        }

        // Filtrado por coordinación si el admin selecciona una
        if ($usuario->esAdministrador() && $request->filled('coordinacion_id')) {
            $grupoIds = Grupo::where('coordinacion_id', $request->coordinacion_id)->pluck('id');
            $query->whereHas('grupos', function ($q) use ($grupoIds) {
                $q->whereIn('grupos.id', $grupoIds);
            });
        }

        $cursos = $query->get();

        // 👇 Aquí está la lógica para mostrar grupos según el rol
        if ($usuario->esAdministrador() && is_null($usuario->coordinacion_id)) {
            $grupos = Grupo::with('coordinacion')->orderBy('nombre')->get();
        } else {
            $grupos = Grupo::with('coordinacion')
                ->where('coordinacion_id', $usuario->coordinacion_id)
                ->orderBy('nombre')
                ->get();
        }

        $coordinaciones = Coordinacion::orderBy('nombre')->get();

        return view('admin.cursos.index', compact('cursos', 'grupos', 'coordinaciones', 'usuario'));
    }


        // Pasar datos a la vista

        return view('admin.cursos.index', [
            'usuario' => $user,
            'coordinaciones' => $coordinaciones,
            'grupos' => $gruposParaFiltro, // <- ESTE lo estás usando en filtros
            'gruposTodos' => Grupo::orderBy('nombre')->get(['id', 'nombre']), // <- ESTE es para el modal
            'cursos' => $cursos,
            'selectedCoordinacionId' => $selectedCoordinacionId,
            'selectedGrupoId' => $selectedGrupoId,
        ]);



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
            'coordinacion_id' => auth()->user()->coordinacion_id, // Sólo si no es admin sin coordinación
        ]);

        $curso->grupos()->attach($request->grupo_ids);

        return redirect()->route('admin.cursos.index')->with('success', 'Curso registrado correctamente.');
    }



    /**
     * Actualiza un curso existente.
     */
    public function update(Request $request, Curso $curso) // Usar Route Model Binding
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

    public function create()
    {
        $user = auth()->user();

        $grupos = $user->esAdministrador() && $user->coordinacion_id === null
            ? Grupo::with('coordinacion')->orderBy('nombre')->get()
            : Grupo::with('coordinacion')->where('coordinacion_id', $user->coordinacion_id)->orderBy('nombre')->get();

        return view('admin.cursos.create', compact('grupos'));
    }


    public function edit(Curso $curso)
    {
        $usuario = Auth::user();

        $grupo_ids = $curso->grupos()->pluck('grupos.id')->toArray();
        $todos_grupos = Grupo::where('coordinacion_id', $usuario->coordinacion_id)
                             ->select('id', 'nombre')
                             ->orderBy('nombre')
                             ->get();

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


    public function destroy(Curso $curso)
    {
        $curso->delete();
        return redirect()->route('admin.cursos.index')->with('success', 'Curso eliminado correctamente.');
    }



}
