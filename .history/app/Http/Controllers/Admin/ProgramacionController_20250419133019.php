<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Programacion;
use App\Models\Grupo;
use App\Models\Instructor;
use App\Models\Aula;
use App\Models\Feriado;
use App\Models\Curso; // Asegúrate de importar Curso
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Para Log::error
use Carbon\Carbon; // Para el cálculo de fechas

class ProgramacionController extends Controller
{

    public function index()
    {
         return redirect()->route('admin.programaciones.create');
    }

    public function create()
    {
        // ... (código existente para obtener $user, $grupos, etc.) ...
        $user = Auth::user();
        $grupos = [];
        if ($user->coordinacion_id) {
             $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)
                           ->with('coordinacion')
                           ->orderBy('nombre')->get();
        } elseif ($user->esAdministrador()) {
             $grupos = Grupo::with('coordinacion')->orderBy('coordinacion_id')->orderBy('nombre')->get();
        }
        // ...

        $instructores = Instructor::where('activo', true)->orderBy('nombre')->get();
        $aulas = Aula::where('activa', true)->orderBy('nombre')->get();
        $feriados = Feriado::pluck('fecha')->map(fn ($fecha) => $fecha->format('Y-m-d'))->toArray();

        return view('admin.programaciones.create', compact(
            'grupos',
            'instructores', // Se pasan todos los activos
            'aulas',
            'feriados'
        ));
    }


    // ... métodos store, show, edit, update, destroy, showProgramarBloque (ya existentes) ...

    // --- INICIO: MÉTODOS PARA LAS RUTAS API ---

    public function getCursosPorGrupoApi(Grupo $grupo)
    {
        $user = Auth::user();
        $puedeAcceder = false;
        if ($user->esAdministrador()) {
            $puedeAcceder = true;
        } elseif ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id) {
            $puedeAcceder = true;
        }

        if (!$puedeAcceder) {
            return response()->json(['error' => 'No autorizado para acceder a los cursos de este grupo.'], 403);
        }

        try {
            $cursos = $grupo->cursos()
                            ->select('cursos.id', 'cursos.nombre', 'cursos.duracion_horas') // Asegúrate que 'duracion_horas' exista en tabla cursos
                            ->orderBy('cursos.nombre')
                            ->get();
            return response()->json($cursos);
        } catch (\Exception $e) {
            Log::error("Error al obtener cursos por grupo: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al obtener los cursos.'], 500);
        }
    }

    public function getInstructoresPorCursoApi(Curso $curso)
    {
        try {
            // Asegúrate que la relación 'instructores' exista en App\Models\Curso
            // y que el modelo Instructor tenga la columna 'activo'
            $instructores = $curso->instructores()
                                  ->where('activo', true) // Filtra por instructores activos
                                  ->select('instructores.id', 'instructores.nombre')
                                  ->orderBy('instructores.nombre')
                                  ->get();
            return response()->json($instructores);
        } catch (\RelationNotFoundException $e) {
             Log::error("Error: Relación 'instructores' no encontrada en el modelo Curso. ID: " . $curso->id);
             // Devuelve un array vacío para que el JS no dé alerta, pero loguea el error
             return response()->json([]); // Devolver array vacío en lugar de error 500
        } catch (\Illuminate\Database\QueryException $e) {
            // Captura específicamente el error si la columna 'activo' no existe
             if(str_contains($e->getMessage(), 'Unknown column') && str_contains($e->getMessage(), 'activo')){
                 Log::error("Error: Columna 'activo' no encontrada en tabla instructores al filtrar por curso (ID: {$curso->id}).");
                 // Intenta obtenerlos sin filtrar por activo como fallback temporal
                 try {
                     $instructores = $curso->instructores()
                                           ->select('instructores.id', 'instructores.nombre')
                                           ->orderBy('instructores.nombre')
                                           ->get();
                     return response()->json($instructores);
                 } catch (\Exception $inner_e) {
                      Log::error("Error (fallback) al obtener instructores por curso (ID: {$curso->id}): " . $inner_e->getMessage());
                       return response()->json([]); // Devolver array vacío
                 }
             } else {
                 // Otro error de base de datos
                 Log::error("Error DB al obtener instructores por curso (ID: {$curso->id}): " . $e->getMessage());
                  return response()->json([]); // Devolver array vacío
             }
        }
         catch (\Exception $e) {
            Log::error("Error general al obtener instructores por curso (ID: {$curso->id}): " . $e->getMessage());
             return response()->json([]); // Devolver array vacío
        }
    }

    public function calcularFechaFinApi(Request $request)
    {
         $validated = $request->validate([
             'inicio' => 'required|date_format:Y-m-d',
             'horas' => 'required|integer|min:1',
             'hora_inicio' => 'required|date_format:H:i',
         ]);

         try {

            $fechaFin = Carbon::parse($validated['inicio'])->addDay()->format('Y-m-d');
            $horaFin = '17:00';

            return response()->json([
                'fecha_fin' => $fechaFin,
                'hora_fin' => $horaFin
            ]);
            // --- FIN RESPUESTA TEMPORAL ---

        } catch (\Exception $e) {
            // El bloque catch se mantiene igual
            Log::error("Error al calcular fecha fin (API): " . $e->getMessage() . " Data: " . json_encode($validated));
            return response()->json(['error' => 'No se pudo procesar el cálculo de fecha.'], 500);
        }
    }

    /**
      * Obtiene los detalles de disponibilidad (eventos y tabla) para un recurso.
      * Responde en formato JSON para llamadas Fetch/Alpine.
      *
      * @param Request $request Espera 'tipo' (instructor|aula) y 'id' (int)
      * @return \Illuminate\Http\JsonResponse
      */
      public function getDetalleDisponibilidadApi(Request $request)
      {
          $validated = $request->validate([
              'tipo' => 'required|in:instructor,aula',
              'id' => 'required|integer|min:1',
          ]);

          $tipo = $validated['tipo'];
          $resourceId = $validated['id'];

          try {
              $eventos = [];
              $tabla = [];

              // --- LÓGICA DESCOMENTADA Y AJUSTADA ---
              $query = Programacion::query()
                  // Cargar relaciones necesarias con select para eficiencia
                  ->with([
                      'curso:id,nombre',
                      'grupo:id,nombre,coordinacion_id', // Necesitamos coordinacion_id para la siguiente relación
                      'grupo.coordinacion:id,nombre,color' // Cargar coordinación a través de grupo
                      // Cargar el recurso opuesto si quieres mostrarlo (ej. instructor si buscas aula)
                      // ($tipo === 'aula' ? 'instructor:id,nombre' : 'aula:id,nombre,lugar')
                  ])
                  ->where($tipo . '_id', $resourceId)
                  // Puedes añadir filtros de fecha si quieres limitar el rango mostrado
                  // ->where('fecha_fin', '>=', now()->startOfMonth()->subMonth()) // Ej: Desde el mes pasado
                  ->orderBy('fecha_inicio', 'asc')
                  ->orderBy('hora_inicio', 'asc');

              $ocupaciones = $query->get(); // Ejecutar la consulta

              foreach ($ocupaciones as $ocupacion) {
                  // Saltar si faltan datos esenciales para la visualización
                  if (!$ocupacion->fecha_inicio || !$ocupacion->fecha_fin || !$ocupacion->hora_inicio || !$ocupacion->hora_fin || !$ocupacion->curso) {
                       Log::warning("Omitiendo ocupación ID {$ocupacion->id} por datos faltantes para modal.");
                       continue;
                  }

                  $fechaInicioFmt = $ocupacion->fecha_inicio->format('d/m/Y');
                  $fechaFinFmt = $ocupacion->fecha_fin->format('d/m/Y');
                  $horaInicioFmt = $ocupacion->hora_inicio->format('H:i');
                  $horaFinFmt = $ocupacion->hora_fin->format('H:i');
                  // Usar ?? para evitar error si la coordinación es null
                  $colorCoord = $ocupacion->grupo?->coordinacion?->color ?? '#6B7280'; // Gris por defecto

                  // Formatear para FullCalendar
                  $eventos[] = [
                      'title' => $ocupacion->curso->nombre, // Usar nombre del curso
                      'start' => $ocupacion->fecha_inicio->format('Y-m-d'),
                      // Para eventos que duran varios días, end es exclusivo
                      'end' => ($fechaInicioFmt !== $fechaFinFmt) ? $ocupacion->fecha_fin->addDay()->format('Y-m-d') : null,
                      'allDay' => ($fechaInicioFmt !== $fechaFinFmt), // Marcar como día completo si dura más de un día
                      'color' => $colorCoord,
                      'backgroundColor' => $colorCoord, // Añadir por si acaso
                      'borderColor' => $colorCoord,     // Añadir por si acaso
                      'extendedProps' => [
                         'grupo' => $ocupacion->grupo?->nombre ?? 'N/A',
                         'coordinacion' => $ocupacion->grupo?->coordinacion?->nombre ?? 'N/A',
                         'color' => $colorCoord,
                         'fecha_inicio_fmt' => $fechaInicioFmt,
                         'fecha_fin_fmt' => $fechaFinFmt,
                         'hora_inicio_fmt' => $horaInicioFmt,
                         'hora_fin_fmt' => $horaFinFmt,
                         // ... (añadir recurso opuesto si lo cargaste con with()) ...
                      ]
                  ];
                  // Formatear para la tabla de detalles
                  $tabla[] = [
                      'fecha' => ($fechaInicioFmt === $fechaFinFmt) ? $fechaInicioFmt : $fechaInicioFmt.' - '.$fechaFinFmt,
                      'hora_inicio' => $horaInicioFmt,
                      'hora_fin' => $horaFinFmt,
                      'curso' => $ocupacion->curso->nombre, // Ya verificamos que existe
                      'coordinacion' => $ocupacion->grupo?->coordinacion?->nombre ?? 'N/A',
                      'color' => $colorCoord,
                  ];
              }
              // --- FIN LÓGICA ---

              return response()->json([
                  'eventos' => $eventos,
                  'tabla' => $tabla,
              ]);

          } catch (\Exception $e) {
              Log::error("Error al obtener detalle disponibilidad ({$tipo} ID: {$resourceId}): " . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
              return response()->json(['error' => 'Error interno al obtener los detalles.'], 500);
          }
      }

     /**
      * Verifica si un recurso (instructor o aula) está disponible en un rango de fechas/horas.
      * Responde en formato JSON para llamadas Fetch/Alpine.
      *
      * @param Request $request
      * @return \Illuminate\Http\JsonResponse
      */
     public function verificarDisponibilidadApi(Request $request)
     {
         $validated = $request->validate([
             'tipo' => 'required|in:instructor,aula',
             'id' => 'required|integer|min:1',
             'fecha_inicio' => 'required|date_format:Y-m-d',
             'fecha_fin' => 'required|date_format:Y-m-d',
             'hora_inicio' => 'required|date_format:H:i',
             'hora_fin' => 'required|date_format:H:i',
             'programacion_id' => 'nullable|integer|min:1', // ID de la programación a excluir (para editar)
         ]);

         try {
             $columnaId = $validated['tipo'] . '_id'; // instructor_id o aula_id
             $resourceId = $validated['id'];
             $inicio = Carbon::parse($validated['fecha_inicio'] . ' ' . $validated['hora_inicio']);
             $fin = Carbon::parse($validated['fecha_fin'] . ' ' . $validated['hora_fin']);
             $programacionIdExcluir = $validated['programacion_id'] ?? null;

             // --- LÓGICA DE VERIFICACIÓN DE SOLAPAMIENTO (CON FECHA Y HORA) ---
             $query = Programacion::where($columnaId, $resourceId)
                 ->where(function ($q) use ($inicio, $fin) {
                     // Condición de solapamiento: El inicio propuesto es ANTES de que termine uno existente
                     // Y el fin propuesto es DESPUÉS de que empiece uno existente.
                     // IMPORTANTE: Usar los accessors inicio_completo/fin_completo o construir los datetime aquí.
                     // Usaremos construcción directa aquí por simplicidad si no tienes los accessors.

                     // Construir los datetime completos para comparar
                     $q->where(function($subQuery) use ($inicio, $fin) {
                          $subQuery->whereRaw('TIMESTAMP(fecha_inicio, hora_inicio) < ?', [$fin]) // El existente empieza antes de que termine el nuevo
                                   ->whereRaw('TIMESTAMP(fecha_fin, hora_fin) > ?', [$inicio]); // El existente termina después de que empiece el nuevo
                     });

                     // Considerar casos límite si es necesario (ej. <= y >=)
                     // ->orWhere(function($subQuery) use ($inicio) { ... }) // Si empieza justo cuando otro termina
                     // ->orWhere(function($subQuery) use ($fin) { ... }) // Si termina justo cuando otro empieza
                 });

             // Si estamos editando, excluimos la programación actual de la verificación
             if ($programacionIdExcluir) {
                 $query->where('id', '!=', $programacionIdExcluir);
             }

             $ocupado = $query->exists();
             // --- FIN LÓGICA DE VERIFICACIÓN ---

             return response()->json(['ocupado' => $ocupado]);

         } catch (\Exception $e) {
             Log::error("Error al verificar disponibilidad (API): " . $e->getMessage() . " Data: " . json_encode($validated));
             // Devolver que no está ocupado para no bloquear al usuario, pero loguear el error
             return response()->json(['ocupado' => false, 'error' => 'Error al verificar disponibilidad'], 500);
         }
     }


} // Fin de la clase ProgramacionController
