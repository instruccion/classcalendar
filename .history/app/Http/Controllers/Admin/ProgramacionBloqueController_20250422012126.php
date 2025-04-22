<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\Curso;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Feriado;

class ProgramacionBloqueController extends Controller
{
    /**
     * Muestra la vista inicial para seleccionar el grupo y los cursos del bloque.
     */
    public function index()
    {
        $user = Auth::user();
        $grupos = [];

        // Lógica de permisos para obtener grupos
        if ($user->esAdministrador()) { // Asume que tienes este método en User.php
            $grupos = Grupo::with('coordinacion') // Cargar coordinación para mostrar nombre
                           ->orderBy('coordinacion_id') // Opcional: ordenar por coordinación
                           ->orderBy('nombre')
                           ->get();
        } elseif ($user->coordinacion_id) { // Asume que el usuario tiene una coordinación asignada
            $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)
                           ->with('coordinacion')
                           ->orderBy('nombre')
                           ->get();
        }
        // Añadir lógica para otros roles si es necesario

        return view('admin.programaciones.bloque.index', compact('grupos'));
    }

    /**
     * Obtiene los cursos disponibles para un grupo y tipo específico (para la API).
     * (Mantenemos tu nombre de método original)
     */
    public function getCursosPorGrupo(Request $request) // Cambiado nombre de parámetro a $request
    {
        $validated = $request->validate([ // Usar $validated para más seguridad
            'grupo_id' => 'required|exists:grupos,id',
            'tipo' => 'nullable|string'
        ]);

        $grupo = Grupo::find($validated['grupo_id']); // Usar find() es suficiente aquí
        $user = Auth::user();

        // Verificar permisos
        if (!$grupo || !($user->esAdministrador() || ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id))) {
            return response()->json(['error' => 'No autorizado o grupo no encontrado.'], 403);
        }

        try {
            $query = $grupo->cursos(); // Obtener la relación BelongsToMany

            if ($request->filled('tipo')) {
                $query->where('cursos.tipo', $validated['tipo']); // Filtrar por tipo si se proporciona
            }

            // Seleccionar solo id y nombre, ordenar
            $cursos = $query->orderBy('cursos.nombre')
                            ->get(['cursos.id', 'cursos.nombre']);

            return response()->json($cursos);

        } catch (\Exception $e) {
            Log::error("Error en getCursosPorGrupo API: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al obtener cursos.'], 500);
        }
    }

    /**
     * Muestra la vista para ordenar los cursos seleccionados.
     * Recibe los IDs de los cursos desde el formulario anterior.
     */
    public function ordenar(Request $request)
    {
        $validated = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'cursos_id' => 'required|array',
            'cursos_id.*' => 'integer|exists:cursos,id', // Validar cada ID en el array
        ]);

        $grupo = Grupo::find($validated['grupo_id']); // Usar find()
        $cursosIds = $validated['cursos_id'];
        $user = Auth::user();

        // Verificar permisos nuevamente por seguridad
        if (!$grupo || !($user->esAdministrador() || ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id))) {
             abort(403, 'No autorizado para este grupo.');
        }

        // Obtener los objetos Curso completos
        // findMany preserva el orden de los IDs pasados si la clave primaria es numérica
        $cursosSeleccionados = Curso::findMany($cursosIds);

        // Opcional: Si necesitas garantizar el orden exacto del array $cursosIds
        // $cursosOrdenados = collect($cursosIds)->map(function ($id) use ($cursosSeleccionados) {
        //        return $cursosSeleccionados->firstWhere('id', $id);
        //    })->filter(); // Filter para quitar nulos si algún ID no se encontró

        // Pasamos $cursosSeleccionados (el orden debería ser el de los IDs)
        $feriados = Feriado::pluck('fecha')->map(fn ($f) => $f->format('Y-m-d'))->toArray();
        return view('admin.programaciones.bloque.ordenar', compact('grupo', 'cursosSeleccionados', 'feriados'));
    }

 

}
