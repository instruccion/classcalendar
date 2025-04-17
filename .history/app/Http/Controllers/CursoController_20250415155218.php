<?php

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
        $user = Auth::user();
        $canViewCoordFilter = ($user->rol === 'administrador'); // O Gate::allows('view-coordination-filter');

        // --- Obtener Datos para los Filtros ---
        $coordinaciones = collect();
        if ($canViewCoordFilter) {
            $coordinaciones = Coordinacion::orderBy('nombre')->get(['id', 'nombre']);
        }

        $selectedCoordinacionId = $request->input('coordinacion_id');
        $selectedGrupoId = $request->input('grupo_id');

        // *** Obtener Grupos para el desplegable de filtro ***
        $gruposQuery = Grupo::query()->orderBy('nombre');
        if ($canViewCoordFilter) {
            if ($selectedCoordinacionId) {
                $gruposQuery->where('coordinacion_id', $selectedCoordinacionId);
            }
        } else {
            if ($user->coordinacion_id) {
                $gruposQuery->where('coordinacion_id', $user->coordinacion_id);
            } else {
                $gruposQuery->whereRaw('1 = 0');
            }
        }
        $gruposParaFiltro = $gruposQuery->get(['id', 'nombre']);

        // --- Consulta Principal de Cursos (Aplicando Scoping y Filtros) ---
        $cursosQuery = Curso::query();

        // *** 1. Scoping Obligatorio por Rol/Permiso ***
        if (!$canViewCoordFilter) {
            if ($user->coordinacion_id) {
                $cursosQuery->whereHas('grupos', function (Builder $query) use ($user) {
                    $query->where('coordinacion_id', $user->coordinacion_id);
                });
            } else {
                $cursosQuery->whereRaw('1 = 0');
            }
        }

        // *** 2. Aplicar Filtros Opcionales del Request ***
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

        // Eager Loading y Paginación
        $cursos = $cursosQuery->with(['grupos' => fn($q) => $q->select('grupos.id', 'grupos.nombre')->orderBy('nombre')])
                              ->orderBy('nombre')
                              ->paginate(20); // Ajusta el número por página

        // Pasar datos a la vista
        return view('admin.cursos.index', [
            'usuario' => $user,
            'coordinaciones' => $coordinaciones,
            'grupos' => $gruposParaFiltro, // Renombrar para claridad en la vista si prefieres
            'cursos' => $cursos,
            'selectedCoordinacionId' => $selectedCoordinacionId, // Necesario para Blade y JS
            'selectedGrupoId' => $selectedGrupoId,           // Necesario para Blade y JS
        ]);
    }

    /**
     * Guarda un nuevo curso.
     */
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

    // Añade aquí create, edit, destroy si los necesitas
}
