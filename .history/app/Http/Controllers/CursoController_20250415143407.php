<?php

// app/Http/Controllers/CursoController.php

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
        $usuario = Auth::user();
        $canViewCoordFilter = ($usuario->rol === 'administrador'); // Verifica si es admin

        // Obtener Datos para los Filtros
        $coordinaciones = collect();
        if ($canViewCoordFilter) {
            // El admin puede ver todas las coordinaciones
            $coordinaciones = Coordinacion::orderBy('nombre')->get(['id', 'nombre']);
        }

        // Filtros de Coordinación y Grupo seleccionados
        $selectedCoordinacionId = $request->input('coordinacion_id');
        $selectedGrupoId = $request->input('grupo_id');

        // Obtener Grupos para el desplegable de filtro
        $gruposQuery = Grupo::query()->orderBy('nombre');

        if ($canViewCoordFilter && $selectedCoordinacionId) {
            $gruposQuery->where('coordinacion_id', $selectedCoordinacionId);
        } elseif (!$canViewCoordFilter) {
            // Los usuarios solo pueden ver los grupos de su coordinación
            $gruposQuery->where('coordinacion_id', $usuario->coordinacion_id);
        }

        $gruposParaFiltro = $gruposQuery->get(['id', 'nombre']);

        // Consulta Principal de Cursos (Con Filtros)
        $cursosQuery = Curso::query();

        // Filtro por Coordinación
        if ($selectedCoordinacionId) {
            $cursosQuery->whereHas('grupos', function ($query) use ($selectedCoordinacionId) {
                $query->where('coordinacion_id', $selectedCoordinacionId);
            });
        }

        // Filtro por Grupo
        if ($selectedGrupoId) {
            $cursosQuery->where('grupo_id', $selectedGrupoId);
        }

        // Paginación y Carga Eager para Grupos
        $cursos = $cursosQuery->with('grupos')->orderBy('nombre')->paginate(20);

        return view('admin.cursos.index', [
            'usuario' => $usuario,
            'coordinaciones' => $coordinaciones,
            'grupos' => $gruposParaFiltro,
            'cursos' => $cursos,
            'selectedCoordinacionId' => $selectedCoordinacionId,
            'selectedGrupoId' => $selectedGrupoId,
        ]);
    }

    /**
     * Almacena un nuevo curso.
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
