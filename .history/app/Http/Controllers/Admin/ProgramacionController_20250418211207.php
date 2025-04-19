<?php

namespace App\Http\Controllers\Admin; // Nota el 'Admin' aquí

use App\Http\Controllers\Controller; // Importante importar la clase base Controller
use App\Models\Programacion;
use App\Models\Grupo;
use App\Models\Instructor;
use App\Models\Aula;
use App\Models\Feriado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Para obtener el usuario autenticado

class ProgramacionController extends Controller
{
    /**
     * Muestra una lista de las programaciones (opcional).
     * Podríamos usarla para mostrar un calendario o tabla de cursos ya programados.
     */
    public function index()
    {
        // TODO: Implementar si se necesita una vista de listado/calendario general
        // Por ahora, podríamos redirigir al formulario de creación
        return redirect()->route('admin.programaciones.create');
    }

    /**
     * Muestra el formulario para crear una nueva programación.
     * Equivalente a tu 'programar_curso.php' cuando no se edita.
     */
    public function create()
    {
        // --- Obtener Datos Necesarios para el Formulario ---

        // 1. Grupos: Solo los grupos a los que el usuario actual tiene acceso
        $user = Auth::user();
        $grupos = [];

        // Lógica de permisos (¡AJUSTAR SEGÚN TU ESTRUCTURA!)
        // Ejemplo 1: Si el usuario tiene una coordinación_id directa
        if ($user->coordinacion_id) {
             $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)
                           ->with('coordinacion') // Cargar nombre de la coordinación
                           ->orderBy('nombre')->get();
        // Ejemplo 2: Si el usuario es admin, puede ver todos los grupos
        } elseif ($user->esAdministrador()) {
             $grupos = Grupo::with('coordinacion')->orderBy('coordinacion_id')->orderBy('nombre')->get();
        // Ejemplo 3: Si el usuario pertenece a múltiples coordinaciones (relación ManyToMany)
        // } elseif ($user->relationCoordinaciones) { // Asumiendo que existe la relación
        //     $coordinacionIds = $user->relationCoordinaciones->pluck('id');
        //     $grupos = Grupo::whereIn('coordinacion_id', $coordinacionIds)
        //                   ->with('coordinacion')
        //                   ->orderBy('coordinacion_id')
        //                   ->orderBy('nombre')->get();
        }
        // Añadir más lógica si 'analista' tiene permisos diferentes

        // 2. Instructores: Todos los instructores ACTIVOS
        $instructores = Instructor::where('activo', true)->orderBy('nombre')->get();

        // 3. Aulas: Todas las aulas ACTIVAS
        $aulas = Aula::where('activa', true)->orderBy('nombre')->get();

        // 4. Feriados: Para cálculos de fechas (podemos pasarlos a JS o usarlos en backend)
        $feriados = Feriado::pluck('fecha')->map(fn ($fecha) => $fecha->format('Y-m-d'))->toArray();


        // --- Devolver la Vista del Formulario ---
        return view('admin.programaciones.create', compact(
            'grupos',
            'instructores',
            'aulas',
            'feriados' // Pasamos los feriados a la vista
        ));
    }

    /**
     * Almacena una nueva programación en la base de datos.
     * Equivalente a la lógica INSERT de 'guardar_programacion.php'.
     */
    public function store(Request $request)
    {
        // TODO: Implementar validación, cálculo de fecha/hora fin,
        //       verificación de disponibilidad y guardado.
        dd($request->all()); // Muestra los datos recibidos del form y detiene (para depurar)
    }

    /**
     * Muestra una programación específica (no se usa normalmente en CRUD web).
     */
    public function show(Programacion $programacion)
    {
        // Podría mostrar un resumen si se necesita
        return view('admin.programaciones.show', compact('programacion'));
    }

    /**
     * Muestra el formulario para editar una programación existente.
     * Equivalente a tu 'programar_curso.php' cuando se pasa un ID.
     */
    public function edit(Programacion $programacion)
    {
        // Similar a create(), pero pasando también $programacion a la vista
        // y pre-seleccionando los valores en el formulario.

        // --- Obtener Datos Necesarios (igual que en create, más el existente) ---
        $user = Auth::user();
        $grupos = [];
        // ... (misma lógica de permisos para grupos que en create) ...
        if ($user->coordinacion_id) {
             $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)->with('coordinacion')->orderBy('nombre')->get();
        } elseif ($user->esAdministrador()) {
             $grupos = Grupo::with('coordinacion')->orderBy('coordinacion_id')->orderBy('nombre')->get();
        }
        // ...

        // Obtener TODOS los instructores y aulas, incluso inactivos,
        // porque el curso podría tener uno inactivo asignado previamente.
        // Marcaremos los inactivos en la vista.
        $instructores = Instructor::orderBy('nombre')->get();
        $aulas = Aula::orderBy('nombre')->get();
        $feriados = Feriado::pluck('fecha')->map(fn ($fecha) => $fecha->format('Y-m-d'))->toArray();

        // Cargar relaciones necesarias del modelo $programacion para la vista
        $programacion->load(['grupo', 'curso', 'instructor', 'aula']);

        // --- Devolver la Vista del Formulario de Edición ---
         return view('admin.programaciones.edit', compact(
            'programacion', // El curso programado a editar
            'grupos',
            'instructores',
            'aulas',
            'feriados'
        ));
    }

    /**
     * Actualiza una programación existente en la base de datos.
     * Equivalente a la lógica UPDATE de 'guardar_programacion.php'.
     */
    public function update(Request $request, Programacion $programacion)
    {
        // TODO: Implementar validación (considerando unique:ignore),
        //       cálculo de fecha/hora fin, verificación de disponibilidad (excluyendo $programacion->id),
        //       y actualización ($programacion->update(...)).
         dd($request->all(), $programacion); // Muestra datos y modelo existente (para depurar)
    }

    /**
     * Elimina una programación de la base de datos.
     * Equivalente a 'eliminar_programacion.php'.
     */
    public function destroy(Programacion $programacion)
    {
        // TODO: Implementar lógica de eliminación
        // try {
        //     $programacion->delete();
        //     return redirect()->route('admin.programaciones.index') // O a donde quieras ir
        //                    ->with('success', 'Programación eliminada correctamente.');
        // } catch (\Exception $e) {
        //     return back()->withErrors(['error' => 'No se pudo eliminar la programación.']);
        // }
        dd("Eliminar programación ID: " . $programacion->id); // Para depurar
    }

    // --- MÉTODOS ADICIONALES (SI LOS NECESITAS) ---

    /**
     * Muestra la vista para iniciar la programación por bloque.
     * Equivalente a 'programar_bloque.php'.
     */
     public function showProgramarBloque()
     {
         // Lógica similar a create() para obtener los grupos disponibles para el usuario
         $user = Auth::user();
         $grupos = [];
         if ($user->coordinacion_id) {
              $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)->with('coordinacion')->orderBy('nombre')->get();
         } elseif ($user->esAdministrador()) {
              $grupos = Grupo::with('coordinacion')->orderBy('coordinacion_id')->orderBy('nombre')->get();
         }
         // ...

         return view('admin.programaciones.programar_bloque', compact('grupos'));
     }


         // --- INICIO: MÉTODOS PARA LAS RUTAS API ---

        /**
         * Obtiene los cursos asociados a un grupo específico,
         * verificando los permisos del usuario actual.
         * Responde en formato JSON para llamadas Fetch/Alpine.
         *
         * @param Grupo $grupo El modelo Grupo inyectado por Route Model Binding.
         * @return \Illuminate\Http\JsonResponse
         */
        public function getCursosPorGrupoApi(Grupo $grupo)
        {
            $user = Auth::user();

            // --- Verificación de Permisos ---
            // ¿Puede el usuario actual ver/usar los cursos de este grupo?
            $puedeAcceder = false;
            if ($user->esAdministrador()) {
                $puedeAcceder = true;
            } elseif ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id) {
                // Asumimos que coordinador/analista pueden acceder a grupos de SU coordinación
                $puedeAcceder = true;
            }
            // TODO: Ajustar esta lógica si hay más reglas (ej. analista, permisos específicos)

            if (!$puedeAcceder) {
                // Si no tiene permiso, devuelve un error 403 (Prohibido)
                return response()->json(['error' => 'No autorizado para acceder a los cursos de este grupo.'], 403);
            }

            // --- Obtener Cursos ---
            try {
                // Asume que tienes una relación llamada 'cursos' en tu modelo Grupo
                // Selecciona solo los campos necesarios para el select
                $cursos = $grupo->cursos()
                                ->select('cursos.id', 'cursos.nombre', 'cursos.duracion_horas')
                                ->orderBy('cursos.nombre')
                                ->get();

                // Devuelve los cursos como JSON
                return response()->json($cursos);

            } catch (\Exception $e) {
                // Manejo básico de errores si la relación no existe o hay otro problema
                // Loguear el error real para depuración
                \Log::error("Error al obtener cursos por grupo: " . $e->getMessage());
                return response()->json(['error' => 'Error interno al obtener los cursos.'], 500);
            }
        }

        // --- FIN: MÉTODOS PARA LAS RUTAS API ---

        // Aquí irían los otros métodos API (getInstructoresPorCursoApi, etc.)
        // que implementaremos después.

    // No olvides la llave de cierre final de la clase ProgramacionController
    }
}
