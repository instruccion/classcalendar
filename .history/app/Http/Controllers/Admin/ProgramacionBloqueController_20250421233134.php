<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\Curso; // Asegúrate de importar Curso
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProgramacionBloqueController extends Controller
{
    /**
     * Muestra la vista inicial para seleccionar el grupo y los cursos del bloque.
     */
    public function index() // Cambiado de showProgramarBloque a index por convención
    {
        $user = Auth::user();
        $grupos = [];

        // Lógica de permisos para obtener grupos
        if ($user->esAdministrador()) {
            $grupos = Grupo::with('coordinacion')->orderBy('coordinacion_id')->orderBy('nombre')->get();
        } elseif ($user->coordinacion_id) {
            $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)
                           ->with('coordinacion')
                           ->orderBy('nombre')->get();
        }
        // Añadir más lógica para otros roles si es necesario

        // Pasar los grupos a la vista
        return view('admin.programaciones.bloque.index', compact('grupos'));
    }

    /**
     * Obtiene los cursos disponibles para un grupo y tipo específico (para la API).
     */
    public function getCursosApi(Request $request) // Cambiado nombre para claridad
    {
        $validated = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'tipo' => 'nullable|string',
        ]);

        $grupo = Grupo::find($validated['grupo_id']);
        $user = Auth::user();

        // Verificar permisos (igual que en index)
        if (!($user->esAdministrador() || ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id))) {
            return response()->json(['error' => 'No autorizado.'], 403);
        }

        try {
            $query = $grupo->cursos();
            if ($request->filled('tipo')) {
                $query->where('cursos.tipo', $validated['tipo']);
            }
            // Seleccionar campos necesarios para la interfaz
            $cursos = $query->orderBy('cursos.nombre')
                            ->get(['cursos.id', 'cursos.nombre']); // No necesitamos duración aquí

            return response()->json($cursos);

        } catch (\Exception $e) {
            Log::error("Error al obtener cursos para bloque API: " . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
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

         $grupo = Grupo::find($validated['grupo_id']);
         $cursosIds = $validated['cursos_id'];

         // Obtener los objetos Curso completos en el orden recibido (o reordenar si es necesario)
         // Usar findMany para obtenerlos por ID y luego reordenar si el orden importa mucho
         // o simplemente obtenerlos y pasarlos a la vista para ordenar con JS/Draggable.
         $cursosSeleccionados = Curso::findMany($cursosIds);

         // Reordenar $cursosSeleccionados para que coincida con el orden de $cursosIds si es necesario
         $cursosOrdenados = collect($cursosIds)->map(function ($id) use ($cursosSeleccionados) {
                return $cursosSeleccionados->firstWhere('id', $id);
            })->filter(); // Filter para quitar nulos si algún ID no se encontró

         // Verificar permisos nuevamente por seguridad
         $user = Auth::user();
         if (!($user->esAdministrador() || ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id))) {
              abort(403, 'No autorizado');
         }

         // TODO: Crear la vista 'admin.programaciones.bloque.ordenar'
         return view('admin.programaciones.bloque.ordenar', compact('grupo', 'cursosOrdenados'));
     }

     // TODO: Añadir un método 'storeBloque' que reciba el orden final,
     //       la fecha/hora inicio, aula, instructor, calcule todas las fechas
     //       verifique disponibilidad y cree las múltiples entradas en 'programaciones'.

}
