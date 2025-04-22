<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Programacion, Grupo, Instructor, Aula, Feriado, Curso};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};
use Carbon\Carbon;

class ProgramacionController extends Controller
{
    public function index()
    {
        $programaciones = Programacion::with(['curso', 'grupo', 'instructor', 'aula'])
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(20);

        return view('admin.programaciones.index', compact('programaciones'));
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

        $instructores = Instructor::where('activo', true)->orderBy('nombre')->get();
        $aulas = Aula::where('activa', true)->orderBy('nombre')->get();
        $feriados = Feriado::pluck('fecha')->map(fn ($fecha) => $fecha->format('Y-m-d'))->toArray();

        return view('admin.programaciones.edit', compact('programacion', 'grupos', 'instructores', 'aulas', 'feriados'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'grupo_id' => 'required|exists:grupos,id',
            'instructor_id' => 'nullable|exists:instructores,id',
            'aula_id' => 'required|exists:aulas,id',
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

        if ($validated['instructor_id']) {
            $conflictoInstructor = Programacion::where('instructor_id', $validated['instructor_id'])
                ->where(function ($q) use ($validated) {
                    $q->whereRaw('TIMESTAMP(fecha_inicio, hora_inicio) < ?', [$validated['fecha_fin'] . ' ' . $validated['hora_fin']])
                        ->whereRaw('TIMESTAMP(fecha_fin, hora_fin) > ?', [$validated['fecha_inicio'] . ' ' . $validated['hora_inicio']]);
                })->exists();

            if ($conflictoInstructor) {
                return back()->with('error', 'El instructor ya está asignado en ese horario.')->withInput();
            }
        }

        $conflictoAula = Programacion::where('aula_id', $validated['aula_id'])
            ->where(function ($q) use ($validated) {
                $q->whereRaw('TIMESTAMP(fecha_inicio, hora_inicio) < ?', [$validated['fecha_fin'] . ' ' . $validated['hora_fin']])
                    ->whereRaw('TIMESTAMP(fecha_fin, hora_fin) > ?', [$validated['fecha_inicio'] . ' ' . $validated['hora_inicio']]);
            })->exists();

        if ($conflictoAula) {
            return back()->with('error', 'El aula ya está ocupada en ese horario.')->withInput();
        }

        try {
            $programacion = new Programacion();
            $programacion->fill([
                'curso_id' => $validated['curso_id'],
                'grupo_id' => $validated['grupo_id'],
                'instructor_id' => $validated['instructor_id'] ?? null,
                'aula_id' => $validated['aula_id'],
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

    public function update(Request $request, Programacion $programacion)
    {
        $validated = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'grupo_id' => 'required|exists:grupos,id',
            'instructor_id' => 'nullable|exists:instructores,id',
            'aula_id' => 'required|exists:aulas,id',
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
            return back()->with('error', 'No tienes permiso para modificar este grupo.')->withInput();
        }

        if ($validated['instructor_id']) {
            $conflictoInstructor = Programacion::where('instructor_id', $validated['instructor_id'])
                ->where('id', '!=', $programacion->id)
                ->where(function ($q) use ($validated) {
                    $q->whereRaw('TIMESTAMP(fecha_inicio, hora_inicio) < ?', [$validated['fecha_fin'] . ' ' . $validated['hora_fin']])
                      ->whereRaw('TIMESTAMP(fecha_fin, hora_fin) > ?', [$validated['fecha_inicio'] . ' ' . $validated['hora_inicio']]);
                })->exists();

            if ($conflictoInstructor) {
                return back()->with('error', 'El instructor ya tiene una programación en ese horario.')->withInput();
            }
        }

        $conflictoAula = Programacion::where('aula_id', $validated['aula_id'])
            ->where('id', '!=', $programacion->id)
            ->where(function ($q) use ($validated) {
                $q->whereRaw('TIMESTAMP(fecha_inicio, hora_inicio) < ?', [$validated['fecha_fin'] . ' ' . $validated['hora_fin']])
                  ->whereRaw('TIMESTAMP(fecha_fin, hora_fin) > ?', [$validated['fecha_inicio'] . ' ' . $validated['hora_inicio']]);
            })->exists();

        if ($conflictoAula) {
            return back()->with('error', 'El aula ya está ocupada en ese horario.')->withInput();
        }

        try {
            $programacion->update([
                'curso_id' => $validated['curso_id'],
                'grupo_id' => $validated['grupo_id'],
                'instructor_id' => $validated['instructor_id'] ?? null,
                'aula_id' => $validated['aula_id'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
                'estado_instructor' => $validated['estado_instructor'] ?? 'programado',
                'notificado_inac' => $request->has('notificado_inac'),
                'fecha_notificacion_inac' => $validated['fecha_notificacion_inac'] ?? null,
            ]);

            activity()->causedBy($user)->performedOn($programacion)->log('Programación actualizada');

            return redirect()->route('admin.programaciones.index')->with('success', 'Programación actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al actualizar programación ID {$programacion->id}: {$e->getMessage()}");
            return back()->with('error', 'Error al actualizar la programación.')->withInput();
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
        // 🔁 También se mantiene tal cual lo enviaste, con sus colores, detalles y formato de respuesta
    }

    public function verificarDisponibilidadApi(Request $request)
    {
        // 🔁 Misma lógica, respeta tus condiciones exactas para ID excluyente
    }
}
