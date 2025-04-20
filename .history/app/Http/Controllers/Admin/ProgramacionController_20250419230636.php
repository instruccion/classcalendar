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
            $minutosTotales = $request->input('horas') * 50;
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
                    $inicioManana = clone $fechaActual;
                    if ($horaActual < '08:30') $fechaActual->setTime(8, 30);
                    $finManana = $fechaActual->copy()->setTime(12, 0);
                    $minutosManana = $fechaActual->diffInMinutes($finManana);
                    $minutosDisponiblesHoy += max(min($minutosManana, 210), 0);
                }

                if ($horaActual >= '12:00' && $horaActual < '13:00') {
                    $fechaActual->setTime(13, 0);
                }

                if ($fechaActual->format('H:i') >= '13:00' && $fechaActual->format('H:i') < '17:00') {
                    $finTarde = $fechaActual->copy()->setTime(17, 0);
                    $minutosTarde = $fechaActual->diffInMinutes($finTarde);
                    $minutosDisponiblesHoy += max(min($minutosTarde, 240), 0);
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
                'fecha_fin' => $horaFin->copy()->format('Y-m-d'),
                'hora_fin' => $horaFin->format('H:i')
            ]);

        } catch (\Exception $e) {
            Log::error("Error al calcular fecha fin (API): " . $e->getMessage());
            return response()->json(['error' => 'No se pudo calcular la fecha de finalización'], 500);
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
