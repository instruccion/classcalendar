<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Programacion, Grupo, Instructor, Aula, Feriado, Curso};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};
use Carbon\Carbon;

class ProgramacionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $coordinaciones = [];
        $grupos = [];

        if ($user->esAdministrador() && is_null($user->coordinacion_id)) {
            $coordinaciones = \App\Models\Coordinacion::orderBy('nombre')->get();

            $grupoQuery = Grupo::with('coordinacion');
            if ($request->filled('coordinacion_id')) {
                $grupoQuery->where('coordinacion_id', $request->coordinacion_id);
            }
            $grupos = $grupoQuery->orderBy('nombre')->get();
        } else {
            $grupos = Grupo::with('coordinacion')
                ->where('coordinacion_id', $user->coordinacion_id)
                ->orderBy('nombre')
                ->get();
        }

        $programacionesQuery = Programacion::with(['curso', 'grupo', 'instructor', 'aula'])
            ->when($request->filled('grupo_id'), function ($q) use ($request) {
                $q->where('grupo_id', $request->grupo_id);
            })
            ->when($request->filled('coordinacion_id') && $user->esAdministrador(), function ($q) use ($request) {
                $q->whereHas('grupo', function ($sub) use ($request) {
                    $sub->where('coordinacion_id', $request->coordinacion_id);
                });
            })
            ->orderBy('fecha_inicio', 'desc');

        // Carga ambos conjuntos:
        $programaciones = $programacionesQuery->paginate(20); // Para la paginación
        $programacionesAgrupadas = $programacionesQuery->get()->groupBy(function ($item) {
            return $item->grupo->nombre ?? 'Sin grupo';
        })->map(function ($bloques) {
            return $bloques->groupBy(function ($prog) {
                return $prog->curso->bloque ?? '—';
            });
        });

        return view('admin.programaciones.index', compact(
            'coordinaciones',
            'grupos',
            'programaciones',
            'programacionesAgrupadas'
        ));
    }



    public function edit(Programacion $programacion)
    {
        $user = Auth::user();
        $grupos = [];

        if ($user->coordinacion_id) {
            $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)
                ->with('coordinacion')->orderBy('nombre')->get();
        } elseif ($user->esAdministrador()) {
            $grupos = Grupo::with('coordinacion')->orderBy('coordinacion_id')->orderBy('nombre')->get();
        }

        // 🔹 Obtener cursos del grupo asignado a la programación
        $cursos = $programacion->grupo ? $programacion->grupo->cursos()->orderBy('nombre')->get() : collect();

        $instructores = Instructor::where('activo', true)->orderBy('nombre')->get();
        $aulas = Aula::where('activa', true)->orderBy('nombre')->get();
        $feriados = Feriado::pluck('fecha')->map(fn ($fecha) => $fecha->format('Y-m-d'))->toArray();

        return view('admin.programaciones.edit', compact(
            'programacion', 'grupos', 'instructores', 'aulas', 'feriados', 'cursos'
        ));
    }


    public function create()
    {
        $user = Auth::user();
        $grupos = [];

        if ($user->coordinacion_id) {
            $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)
                ->with('coordinacion')->orderBy('nombre')->get();
        } elseif ($user->esAdministrador()) {
            $grupos = Grupo::with('coordinacion')->orderBy('coordinacion_id')->orderBy('nombre')->get();
        }

        $instructores = Instructor::where('activo', true)->orderBy('nombre')->get();
        $aulas = Aula::where('activa', true)->orderBy('nombre')->get();
        $feriados = Feriado::pluck('fecha')->map(fn ($fecha) => $fecha->format('Y-m-d'))->toArray();

        return view('admin.programaciones.create', compact('grupos', 'instructores', 'aulas', 'feriados'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'grupo_id' => 'required|exists:grupos,id',
            'aula_id' => 'nullable|exists:aulas,id',
            'instructor_id' => 'nullable|exists:instructores,id',
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'estado_instructor' => 'nullable|in:programado,confirmado',
            'notificado_inac' => 'nullable|boolean',
            'fecha_notificacion_inac' => 'nullable|date_format:Y-m-d',
        ]);

        $user = Auth::user();

        $grupo = Grupo::with('coordinacion')->findOrFail($validated['grupo_id']);
        if (!($user->esAdministrador() || $user->coordinacion_id === $grupo->coordinacion_id)) {
            return back()->with('error', 'No tienes permiso para asignar programaciones a este grupo.');
        }

        if (!empty($validated['instructor_id'])) {
            $conflictoInstructor = Programacion::where('instructor_id', $validated['instructor_id'])
                ->where(function ($q) use ($validated) {
                    $q->whereRaw('TIMESTAMP(fecha_inicio, hora_inicio) < ?', [$validated['fecha_fin'] . ' ' . $validated['hora_fin']])
                      ->whereRaw('TIMESTAMP(fecha_fin, hora_fin) > ?', [$validated['fecha_inicio'] . ' ' . $validated['hora_inicio']]);
                })->exists();

            if ($conflictoInstructor) {
                // Solo advertencia si deseas mostrar un toast, pero no abortar
                session()->flash('warning', '⚠️ El instructor ya está asignado en ese horario.');
            }
        }

        if (!empty($validated['aula_id'])) {
            $conflictoAula = Programacion::where('aula_id', $validated['aula_id'])
                ->where(function ($q) use ($validated) {
                    $q->whereRaw('TIMESTAMP(fecha_inicio, hora_inicio) < ?', [$validated['fecha_fin'] . ' ' . $validated['hora_fin']])
                      ->whereRaw('TIMESTAMP(fecha_fin, hora_fin) > ?', [$validated['fecha_inicio'] . ' ' . $validated['hora_inicio']]);
                })->exists();

            if ($conflictoAula) {
                session()->flash('warning', '⚠️ El aula ya está ocupada en ese horario.');
            }
        }

        try {
            $programacion = new Programacion();
            $programacion->fill([
                'curso_id' => $validated['curso_id'],
                'grupo_id' => $validated['grupo_id'],
                'instructor_id' => $validated['instructor_id'] ?? null,
                'aula_id' => $validated['aula_id'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
                'estado_instructor' => $validated['estado_instructor'] ?? 'programado',
                'notificado_inac' => $request->has('notificado_inac'),
                'fecha_notificacion_inac' => $validated['fecha_notificacion_inac'] ?? null,
            ]);
            $programacion->save();

            activity()->causedBy($user)->performedOn($programacion)->log('Programación creada');

            return redirect()->route('admin.programaciones.index')->with('success', 'Programación registrada correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al guardar programación: {$e->getMessage()}");
            return back()->with('error', 'Error interno al guardar la programación.')->withInput();
        }
    }


    // 🔁 API endpoints (ya los tenías bien, aquí se mantienen tal cual)
    public function getCursosPorGrupoApi(Grupo $grupo)
    {
        $user = Auth::user();

        if (!($user->esAdministrador() || ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id))) {
            return response()->json(['error' => 'No autorizado para acceder a los cursos de este grupo.'], 403);
        }

        try {
            $cursos = $grupo->cursos()->get(['cursos.id', 'cursos.nombre', 'cursos.duracion_horas']);
            return response()->json($cursos);
        } catch (\Exception $e) {
            Log::error("Error al obtener cursos por grupo: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al obtener los cursos.'], 500);
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
            $feriados = Feriado::pluck('fecha')->map(fn ($f) => $f->format('Y-m-d'))->toArray();

            $fechaInicio = Carbon::parse($validated['inicio'] . ' ' . $validated['hora_inicio']);
            $duracionHorasAcademicas = $validated['horas'];
            $minutosTotalesRequeridos = $duracionHorasAcademicas * 50;

            // Asegurarse que la hora de inicio sea válida (dentro del horario laboral)
             if ($fechaInicio->format('H:i') < '08:30') {
                $fechaInicio->setTime(8, 30);
             } elseif ($fechaInicio->format('H:i') >= '12:00' && $fechaInicio->format('H:i') < '13:00') {
                 // Si empieza en hora de almuerzo, mover a la 1 PM
                 $fechaInicio->setTime(13, 0);
             } elseif ($fechaInicio->format('H:i') >= '17:00') {
                 // Si empieza después de las 5 PM, mover al día siguiente hábil a las 8:30 AM
                 $fechaInicio->addDay()->setTime(8, 30);
                 while ($fechaInicio->isWeekend() || in_array($fechaInicio->format('Y-m-d'), $feriados)) {
                     $fechaInicio->addDay();
                 }
             }


             $fechaActual = $fechaInicio->copy(); // Ya está inicializada y ajustada
             $minutosAcumulados = 0;
             $minutosNecesarios = $minutosTotalesRequeridos; // Usar una variable separada

             while ($minutosAcumulados < $minutosNecesarios) {
                 // Saltar fines de semana y feriados
                 if ($fechaActual->isWeekend() || in_array($fechaActual->format('Y-m-d'), $feriados)) {
                     $fechaActual->addDay()->setTime(8, 30);
                     continue;
                 }

                 // Definir bloques de trabajo del día actual
                 $inicioManana = $fechaActual->copy()->setTime(8, 30);
                 $finManana    = $fechaActual->copy()->setTime(12, 0);
                 $inicioTarde  = $fechaActual->copy()->setTime(13, 0);
                 $finTarde     = $fechaActual->copy()->setTime(17, 0);

                 $minutosPorAsignarEsteCiclo = $minutosNecesarios - $minutosAcumulados; // Minutos que aún faltan

                 // --- Mañana (08:30 - 12:00) ---
                 if ($fechaActual->lt($finManana)) { // Si la hora actual es antes de las 12:00
                      // Asegurarse que no empiece antes de las 8:30
                     if ($fechaActual->lt($inicioManana)) {
                         $fechaActual->setTime(8, 30);
                     }
                     $minutosDisponiblesBloque = $fechaActual->diffInMinutes($finManana);
                     $minutosAUsar = min($minutosPorAsignarEsteCiclo, $minutosDisponiblesBloque);

                     if ($minutosAUsar > 0) {
                          $fechaActual->addMinutes($minutosAUsar);
                          $minutosAcumulados += $minutosAUsar;
                          $minutosPorAsignarEsteCiclo -= $minutosAUsar; // Actualizar lo que falta
                          if ($minutosAcumulados >= $minutosNecesarios) break; // Terminado
                     }
                 }

                 // --- Saltar Almuerzo (12:00 - 13:00) ---
                 if ($fechaActual->format('H:i') >= '12:00' && $fechaActual->format('H:i') < '13:00') {
                     $fechaActual->setTime(13, 0);
                 }

                 // --- Tarde (13:00 - 17:00) ---
                 if ($fechaActual->lt($finTarde)) { // Si la hora actual es antes de las 17:00
                     // Asegurarse que no empiece antes de las 13:00 (ya lo hicimos antes, pero por si acaso)
                     if ($fechaActual->lt($inicioTarde)) {
                          $fechaActual->setTime(13, 0);
                     }
                     $minutosDisponiblesBloque = $fechaActual->diffInMinutes($finTarde);
                     $minutosAUsar = min($minutosPorAsignarEsteCiclo, $minutosDisponiblesBloque);

                     if ($minutosAUsar > 0) {
                         $fechaActual->addMinutes($minutosAUsar);
                         $minutosAcumulados += $minutosAUsar;
                         // $minutosPorAsignarEsteCiclo -= $minutosAUsar; // No necesario actualizar aquí si ya salimos
                         if ($minutosAcumulados >= $minutosNecesarios) break; // Terminado
                     }
                 }

                 // --- Pasar al día siguiente si no hemos terminado ---
                  if ($minutosAcumulados < $minutosNecesarios) {
                      $fechaActual->addDay()->setTime(8, 30);
                  }

             } // Fin del while

             // $fechaActual ahora contiene la fecha y hora exactas de finalización
             return response()->json([
                 'fecha_fin' => $fechaActual->format('Y-m-d'),
                 'hora_fin' => $fechaActual->format('H:i')
             ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Error de validación al calcular fecha fin (API): " . json_encode($e->errors()));
            return response()->json(['error' => 'Datos inválidos para calcular fecha.', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Error al calcular fecha fin (API): " . $e->getMessage() . " Line: " . $e->getLine() . " Data: " . json_encode($request->all()));
            return response()->json(['error' => 'No se pudo procesar el cálculo de fecha.'], 500);
        }
    }



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

            $query = Programacion::query()
                ->with([
                    'curso:id,nombre',
                    'grupo:id,nombre,coordinacion_id',
                    'grupo.coordinacion:id,nombre,color'
                ])
                ->where($tipo . '_id', $resourceId)
                ->orderBy('fecha_inicio', 'asc')
                ->orderBy('hora_inicio', 'asc');

            $ocupaciones = $query->get();

            foreach ($ocupaciones as $ocupacion) {
                if (!$ocupacion->fecha_inicio || !$ocupacion->fecha_fin || !$ocupacion->hora_inicio || !$ocupacion->hora_fin || !$ocupacion->curso) {
                    Log::warning("Ocupación incompleta: ID {$ocupacion->id}");
                    continue;
                }

                $fechaInicioFmt = $ocupacion->fecha_inicio->format('d/m/Y');
                $fechaFinFmt = $ocupacion->fecha_fin->format('d/m/Y');
                $horaInicioFmt = $ocupacion->hora_inicio->format('H:i');
                $horaFinFmt = $ocupacion->hora_fin->format('H:i');
                $colorCoord = $ocupacion->grupo?->coordinacion?->color ?? '#6B7280';

                $eventos[] = [
                    'title' => $ocupacion->curso->nombre,
                    'start' => $ocupacion->fecha_inicio->format('Y-m-d'),
                    'end' => ($fechaInicioFmt !== $fechaFinFmt) ? $ocupacion->fecha_fin->addDay()->format('Y-m-d') : null,
                    'allDay' => ($fechaInicioFmt !== $fechaFinFmt),
                    'color' => $colorCoord,
                    'extendedProps' => [
                        'grupo' => $ocupacion->grupo?->nombre ?? 'N/A',
                        'coordinacion' => $ocupacion->grupo?->coordinacion?->nombre ?? 'N/A',
                        'fecha_inicio_fmt' => $fechaInicioFmt,
                        'fecha_fin_fmt' => $fechaFinFmt,
                        'hora_inicio_fmt' => $horaInicioFmt,
                        'hora_fin_fmt' => $horaFinFmt
                    ]
                ];

                $tabla[] = [
                    'fecha' => ($fechaInicioFmt === $fechaFinFmt) ? $fechaInicioFmt : $fechaInicioFmt.' - '.$fechaFinFmt,
                    'hora_inicio' => $horaInicioFmt,
                    'hora_fin' => $horaFinFmt,
                    'curso' => $ocupacion->curso->nombre,
                    'coordinacion' => $ocupacion->grupo?->coordinacion?->nombre ?? 'N/A',
                    'color' => $colorCoord,
                ];
            }

            return response()->json([
                'eventos' => $eventos,
                'tabla' => $tabla,
            ]);

        } catch (\Exception $e) {
            Log::error("Error al obtener detalle disponibilidad ({$tipo} ID: {$resourceId}): " . $e->getMessage());
            return response()->json(['error' => 'Error interno al obtener los detalles.'], 500);
        }
    }

    public function verificarDisponibilidadApi(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:instructor,aula',
            'id' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i',
            'programacion_id' => 'nullable|integer|min:1'
        ]);

        try {
            $columnaId = $validated['tipo'] . '_id';
            $resourceId = $validated['id'];
            $inicio = Carbon::parse($validated['fecha_inicio'] . ' ' . $validated['hora_inicio']);
            $fin = Carbon::parse($validated['fecha_fin'] . ' ' . $validated['hora_fin']);

            $query = Programacion::where($columnaId, $resourceId)
                ->where(function ($q) use ($inicio, $fin) {
                    $q->whereRaw('TIMESTAMP(fecha_inicio, hora_inicio) < ?', [$fin])
                      ->whereRaw('TIMESTAMP(fecha_fin, hora_fin) > ?', [$inicio]);
                });

            if (!empty($validated['programacion_id'])) {
                $query->where('id', '!=', $validated['programacion_id']);
            }

            return response()->json(['ocupado' => $query->exists()]);

        } catch (\Exception $e) {
            Log::error("Error al verificar disponibilidad: " . $e->getMessage());
            return response()->json(['ocupado' => false, 'error' => 'Error interno'], 500);
        }
    }
}
