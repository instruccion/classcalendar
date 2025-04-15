<?php

    namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $canViewCoordFilter = ($user->rol === 'administrador'); // Si es admin, muestra el filtro de coordinación

        // Obtener los datos para los filtros
        $coordinaciones = collect();
        if ($canViewCoordFilter) {
            $coordinaciones = Coordinacion::orderBy('nombre')->get(['id', 'nombre']);
        }

        $selectedCoordinacionId = $request->input('coordinacion_id');
        $selectedGrupoId = $request->input('grupo_id');

        // Obtener los grupos para el filtro
        $gruposQuery = Grupo::query()->orderBy('nombre');
        if ($canViewCoordFilter) {
            if ($selectedCoordinacionId) {
                $gruposQuery->where('coordinacion_id', $selectedCoordinacionId);
            }
        } else {
            if ($user->coordinacion_id) {
                $gruposQuery->where('coordinacion_id', $user->coordinacion_id);
            } else {
                $gruposQuery->whereRaw('1 = 0'); // Si no tiene coordinación, no puede ver grupos
            }
        }
        $gruposParaFiltro = $gruposQuery->get(['id', 'nombre']);

        // Consulta de cursos con filtros
        $cursosQuery = Curso::query();

        // Si no es admin, filtra por la coordinación del usuario
        if (!$canViewCoordFilter) {
            if ($user->coordinacion_id) {
                $cursosQuery->whereHas('grupos', function (Builder $query) use ($user) {
                    $query->where('coordinacion_id', $user->coordinacion_id);
                });
            } else {
                $cursosQuery->whereRaw('1 = 0');
            }
        }

        // Filtros opcionales de coordinación y grupo
        if ($canViewCoordFilter && $selectedCoordinacionId) {
            $cursosQuery->whereHas('grupos', function (Builder $query) use ($selectedCoordinacionId) {
                $query->where('coordinacion_id', $selectedCoordinacionId);
            });
        }
        if ($selectedGrupoId) {
            $cursosQuery->whereHas('grupos', function (Builder $query) use ($selectedGrupoId) {
                $query->where('grupos.id', $selectedGrupoId);
            });
        }

        // Eager loading de los grupos y paginación
        $cursos = $cursosQuery->with(['grupos' => fn($q) => $q->select('grupos.id', 'grupos.nombre')->orderBy('nombre')])
                            ->orderBy('nombre')
                            ->paginate(20); // Puedes ajustar el número de cursos por página

        return view('admin.cursos.index', [
            'usuario' => $user,
            'coordinaciones' => $coordinaciones,
            'grupos' => $gruposParaFiltro,
            'cursos' => $cursos,
            'selectedCoordinacionId' => $selectedCoordinacionId,
            'selectedGrupoId' => $selectedGrupoId,
        ]);
    }


    // Método para guardar un nuevo curso
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|string|in:inicial,recurrente,puntual',
            'descripcion' => 'nullable|string',
            'duracion_horas' => 'required|integer|min:1',
            'grupo_ids' => 'required|array|min:1',
            'grupo_ids.*' => 'required|exists:grupos,id',
        ]);

        $curso = Curso::create($validated);
        $curso->grupos()->sync($validated['grupo_ids']);

        return redirect()->route('admin.cursos.index')->with('success', 'Curso creado exitosamente.');
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

    // Métodos adicionales para crear, editar, eliminar cursos si se requieren
}
