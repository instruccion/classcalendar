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
        $canViewCoordFilter = ($user->rol === 'administrador'); // El administrador puede ver todos los filtros de coordinación

        // --- Obtener Datos para los Filtros ---
        $coordinaciones = collect();
        if ($canViewCoordFilter) {
            $coordinaciones = Coordinacion::orderBy('nombre')->get(['id', 'nombre']);
        }

        $selectedCoordinacionId = $request->input('coordinacion_id');
        $selectedGrupoId = $request->input('grupo_id');

        // Obtener los grupos según la coordinación seleccionada
        $gruposQuery = Grupo::query()->orderBy('nombre');
        if ($canViewCoordFilter) {
            if ($selectedCoordinacionId) {
                $gruposQuery->where('coordinacion_id', $selectedCoordinacionId);
            }
        } else {
            if ($user->coordinacion_id) {
                $gruposQuery->where('coordinacion_id', $user->coordinacion_id);
            } else {
                $gruposQuery->whereRaw('1 = 0'); // No muestra ningún grupo si no tiene coordinacion
            }
        }
        $gruposParaFiltro = $gruposQuery->get(['id', 'nombre']);

        // --- Consulta Principal de Cursos (Aplicando Scoping y Filtros) ---
        $cursosQuery = Curso::query();

        // Si no es administrador, filtrar por la coordinación del usuario
        if (!$canViewCoordFilter) {
            if ($user->coordinacion_id) {
                $cursosQuery->whereHas('grupos', function ($query) use ($user) {
                    $query->where('coordinacion_id', $user->coordinacion_id);
                });
            } else {
                $cursosQuery->whereRaw('1 = 0');
            }
        }

        // Filtrar por Coordinación si se seleccionó
        if ($canViewCoordFilter && $selectedCoordinacionId) {
            $cursosQuery->whereHas('grupos', function ($query) use ($selectedCoordinacionId) {
                $query->where('coordinacion_id', $selectedCoordinacionId);
            });
        }

        // Filtrar por Grupo si se seleccionó
        if ($selectedGrupoId) {
            $cursosQuery->whereHas('grupos', function ($query) use ($selectedGrupoId) {
                $query->where('grupos.id', $selectedGrupoId);
            });
        }

        // Cargar los cursos con sus grupos asociados
        $cursos = $cursosQuery->with(['grupos' => fn($q) => $q->orderBy('nombre')])
                              ->orderBy('nombre')
                              ->paginate(20);

        // Pasar datos a la vista
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
