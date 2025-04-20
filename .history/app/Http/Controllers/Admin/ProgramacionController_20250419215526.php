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
        return redirect()->route('admin.programaciones.create');
    }

    public function create()
    {
        $user = Auth::user();
        $grupos = [];
        if ($user->coordinacion_id) {
            $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)
                ->with('coordinacion')
                ->orderBy('nombre')->get();
        } elseif ($user->esAdministrador()) {
            $grupos = Grupo::with('coordinacion')
                ->orderBy('coordinacion_id')->orderBy('nombre')->get();
        }

        $instructores = Instructor::where('activo', true)->orderBy('nombre')->get();
        $aulas = Aula::where('activa', true)->orderBy('nombre')->get();
        $feriados = Feriado::pluck('fecha')->map(fn ($fecha) => $fecha->format('Y-m-d'))->toArray();

        return view('admin.programaciones.create', compact('grupos', 'instructores', 'aulas', 'feriados'));
    }

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
        $request->validate([
            'inicio' => 'required|date_format:Y-m-d',
            'horas' => 'required|integer|min:1',
            'hora_inicio' => 'required|date_format:H:i',
        ]);

        try {
            $feriados = Feriado::pluck('fecha')->map(fn ($f) => $f->format('Y-m-d'))->toArray();

            $fechaInicio = Carbon::parse($request->input('inicio') . ' ' . $request->input('hora_inicio'));
            $minutosTotales = $request->input('horas') * 45;
            $fechaActual = $fechaInicio->copy();
            $minutosRestantes = $minutosTotales;
            $horaFin = null;

            while ($minutosRestantes > 0) {
                if ($fechaActual->isWeekend() || in_array($fechaActual->format('Y-m-d'), $feriados)) {
                    $fechaActual->addDay()->setTime(8, 30);
                    continue;
                }

                $minutosDisponiblesHoy = 0;
                $horaActual = $fechaActual->format('H:i');

                if ($horaActual < '12:00') {
                    $minutosAntesAlmuerzo = (12 * 60) - ($fechaActual->hour * 60 + $fechaActual->minute);
                    $minutosDisponiblesHoy += max($minutosAntesAlmuerzo, 0);
                    $fechaActual->setTime(13, 0);
                } elseif ($horaActual >= '12:00' && $horaActual < '13:00') {
                    $fechaActual->setTime(13, 0);
                }

                if ($fechaActual->format('H:i') >= '13:00' && $fechaActual->format('H:i') < '17:00') {
                    $minutosAntesFin = (17 * 60) - ($fechaActual->hour * 60 + $fechaActual->minute);
                    $minutosDisponiblesHoy += max($minutosAntesFin, 0);
                }

                if ($minutosDisponiblesHoy <= 0) {
                    $fechaActual->addDay()->setTime(8, 30);
                    continue;
                }

                if ($minutosRestantes <= $minutosDisponiblesHoy) {
                    $horaFin = $fechaActual->copy()->addMinutes($minutosRestantes);
                    $minutosRestantes = 0;
                } else {
                    $minutosRestantes -= $minutosDisponiblesHoy;
                    $fechaActual->addDay()->setTime(8, 30);
                }
            }

            return response()->json([
                'fecha_fin' => $horaFin->copy()->startOfDay()->format('Y-m-d'),
                'hora_fin' => $horaFin->format('H:i')
            ]);

        } catch (\Exception $e) {
            Log::error("Error al calcular fecha fin (API): " . $e->getMessage());
            return response()->json(['error' => 'No se pudo calcular la fecha de finalización'], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grupo_id'      => 'required|exists:grupos,id',
            'curso_id'      => 'required|exists:cursos,id',
            'bloque_codigo' => 'nullable|string|max:191',
            'fecha_inicio'  => 'required|date_format:Y-m-d',
            'hora_inicio'   => 'required|date_format:H:i',
            'aula_id'       => 'nullable|exists:aulas,id',
            'instructor_id' => 'nullable|exists:instructores,id',
        ]);

        $curso = Curso::find($validated['curso_id']);
        $grupo = Grupo::with('coordinacion')->find($validated['grupo_id']);
        $aula = $validated['aula_id'] ? Aula::find($validated['aula_id']) : null;
        $instructor = $validated['instructor_id'] ? Instructor::find($validated['instructor_id']) : null;
        $user = Auth::user();

        $fechaInicio = Carbon::parse($validated['fecha_inicio'] . ' ' . $validated['hora_inicio']);
        $minutosTotales = $curso->duracion_horas * 45;
        $fechaActual = $fechaInicio->copy();
        $minutosRestantes = $minutosTotales;
        $horaFin = null;
        $feriados = Feriado::pluck('fecha')->map(fn ($f) => $f->format('Y-m-d'))->toArray();

        while ($minutosRestantes > 0) {
            if ($fechaActual->isWeekend() || in_array($fechaActual->format('Y-m-d'), $feriados)) {
                $fechaActual->addDay()->setTime(8, 30);
                continue;
            }

            $minutosDisponiblesHoy = 0;
            $horaActual = $fechaActual->format('H:i');

            if ($horaActual < '12:00') {
                $minutosAntesAlmuerzo = (12 * 60) - ($fechaActual->hour * 60 + $fechaActual->minute);
                $minutosDisponiblesHoy += max($minutosAntesAlmuerzo, 0);
                $fechaActual->setTime(13, 0);
            } elseif ($horaActual >= '12:00' && $horaActual < '13:00') {
                $fechaActual->setTime(13, 0);
            }

            if ($fechaActual->format('H:i') >= '13:00' && $fechaActual->format('H:i') < '17:00') {
                $minutosAntesFin = (17 * 60) - ($fechaActual->hour * 60 + $fechaActual->minute);
                $minutosDisponiblesHoy += max($minutosAntesFin, 0);
            }

            if ($minutosDisponiblesHoy <= 0) {
                $fechaActual->addDay()->setTime(8, 30);
                continue;
            }

            if ($minutosRestantes <= $minutosDisponiblesHoy) {
                $horaFin = $fechaActual->copy()->addMinutes($minutosRestantes);
                $minutosRestantes = 0;
            } else {
                $minutosRestantes -= $minutosDisponiblesHoy;
                $fechaActual->addDay()->setTime(8, 30);
            }
        }

        $fechaFinCalculada = $horaFin->copy()->startOfDay();
        $horaFinCalculada = $horaFin->format('H:i:s');

        $programacion = Programacion::create([
            'grupo_id' => $grupo->id,
            'curso_id' => $curso->id,
            'bloque_codigo' => $request->input('bloque_codigo'),
            'fecha_inicio' => $validated['fecha_inicio'],
            'hora_inicio' => $validated['hora_inicio'],
            'fecha_fin' => $fechaFinCalculada->format('Y-m-d'),
            'hora_fin' => $horaFinCalculada,
            'aula_id' => $aula?->id,
            'instructor_id' => $instructor?->id,
            'estado' => 'programado',
            'coordinacion_id' => $grupo->coordinacion_id,
            'user_id' => $user->id,
            'notificado_inac' => false,
        ]);

        if (function_exists('registrar_auditoria')) {
            registrar_auditoria(
                "Programación Creada",
                "Curso '{$curso->nombre}' programado para Grupo '{$grupo->nombre}' del {$validated['fecha_inicio']} al {$fechaFinCalculada->format('Y-m-d')}. ID: {$programacion->id}"
            );
        } else {
            Log::info("Programación creada: ID {$programacion->id}");
        }

        return redirect()->route('admin.programaciones.create')
            ->with('success', '¡Curso programado exitosamente!');
    }
}
