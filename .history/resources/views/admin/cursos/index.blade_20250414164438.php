<?php

namespace App\Http\Controllers\Admin; // Ajusta el namespace

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder; // Importante

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->rol === 'administrador';

        // --- Obtener Datos para los Filtros ---
        $coordinaciones = collect(); // Inicializar colección vacía
        if ($isAdmin) {
            // Solo el admin ve todas las coordinaciones en el filtro
            $coordinaciones = Coordinacion::orderBy('nombre')->get(['id', 'nombre']);
        }

        // Obtener IDs seleccionados de los filtros (si existen)
        $selectedCoordinacionId = $request->input('coordinacion_id');
        $selectedGrupoId = $request->input('grupo_id');

        // Obtener Grupos para el filtro (Depende de la selección de coordinación o rol)
        $gruposQuery = Grupo::query()->orderBy('nombre');

        if ($isAdmin) {
            // Si es admin y seleccionó una coordinación específica, filtrar grupos
            if ($selectedCoordinacionId) {
                $gruposQuery->where('coordinacion_id', $selectedCoordinacionId);
            }
            // Si es admin y NO seleccionó coordinación, muestra todos los grupos
        } else {
            // Si NO es admin, SIEMPRE filtrar por su coordinación
            if ($user->coordinacion_id) {
                $gruposQuery->where('coordinacion_id', $user->coordinacion_id);
            } else {
                $gruposQuery->whereRaw('1 = 0'); // No mostrar grupos si no tiene coordinación asignada
            }
        }
        $gruposParaFiltro = $gruposQuery->get(['id', 'nombre']);


        // --- Consulta Principal de Cursos (con Scoping y Filtros) ---
        $cursosQuery = Curso::query();

        // **Scoping de Seguridad Obligatorio para NO-ADMINS**
        if (!$isAdmin) {
            if ($user->coordinacion_id) {
                // Filtrar cursos que pertenezcan a grupos de la coordinación del usuario
                // Asume relación Curso -> BelongsToMany -> Grupo -> BelongsTo -> Coordinacion
                $cursosQuery->whereHas('grupos', function (Builder $query) use ($user) {
                    $query->where('coordinacion_id', $user->coordinacion_id);
                });
                // Si un curso puede pertenecer directamente a una coordinación, añade esa lógica también
            } else {
                // Si no es admin y no tiene coordinación, no debería ver ningún curso
                $cursosQuery->whereRaw('1 = 0'); // Manera segura de no devolver nada
            }
        }

        // **Aplicar Filtros Opcionales (Principalmente para Admin)**
        if ($selectedCoordinacionId) {
             // Si se seleccionó una coordinación en el filtro (admin)
             $cursosQuery->whereHas('grupos', function (Builder $query) use ($selectedCoordinacionId) {
                $query->where('coordinacion_id', $selectedCoordinacionId);
            });
        }

        if ($selectedGrupoId) {
            // Si se seleccionó un grupo en el filtro
            $cursosQuery->whereHas('grupos', function (Builder $query) use ($selectedGrupoId) {
                $query->where('grupos.id', $selectedGrupoId); // Asegúrate que 'grupos.id' es correcto
            });
        }

        // **Eager Loading para evitar N+1**
        $cursos = $cursosQuery->with('grupos:id,nombre') // Solo trae id y nombre de grupos
                              ->orderBy('nombre') // O el orden que prefieras
                              ->paginate(15); // O ->get() si no necesitas paginación

        // Pasar datos a la vista
        return view('admin.cursos.index', [
            'cursos' => $cursos,
            'coordinaciones' => $coordinaciones, // Solo se llena si es admin
            'grupos' => $gruposParaFiltro,       // Grupos filtrados para el select
            'usuario' => $user,                  // Pasar el usuario para la vista (o usar Auth::user() directamente)
            'selectedCoordinacionId' => $selectedCoordinacionId, // Para mantener selección en el filtro
            'selectedGrupoId' => $selectedGrupoId,           // Para mantener selección en el filtro
        ]);
    }

    // ... otros métodos del controlador (create, store, edit, update, destroy) ...
}
