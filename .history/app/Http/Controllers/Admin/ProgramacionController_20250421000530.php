<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Programacion, Grupo, Instructor, Aula, Feriado, Curso, Coordinacion};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};
use Carbon\Carbon;

class ProgramacionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Programacion::with(['grupo.coordinacion', 'curso', 'aula', 'instructor']);

        if ($user->esAdministrador() && is_null($user->coordinacion_id)) {
            if ($request->filled('coordinacion_id')) {
                $query->whereHas('grupo', function ($q) use ($request) {
                    $q->where('coordinacion_id', $request->coordinacion_id);
                });
            }
        } else {
            $query->whereHas('grupo', function ($q) use ($user) {
                $q->where('coordinacion_id', $user->coordinacion_id);
            });
        }

        if ($request->filled('grupo_id')) {
            $query->where('grupo_id', $request->grupo_id);
        }

        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('grupo', fn($sub) => $sub->where('nombre', 'like', "%{$busqueda}%"))
                  ->orWhereHas('curso', fn($sub) => $sub->where('nombre', 'like', "%{$busqueda}%"))
                  ->orWhereHas('instructor', fn($sub) => $sub->where('nombre', 'like', "%{$busqueda}%"));
            });
        }

        $query->whereMonth('fecha_inicio', $request->get('mes', now()->month));
        $query->whereYear('fecha_inicio', $request->get('anio', now()->year));

        $programaciones = $query->orderBy('fecha_inicio', 'desc')->paginate(15);

        $coordinaciones = [];
        $grupos = [];

        if ($user->esAdministrador()) {
            $coordinaciones = Coordinacion::orderBy('nombre')->get();

            if ($request->filled('coordinacion_id')) {
                $grupos = Grupo::where('coordinacion_id', $request->coordinacion_id)->orderBy('nombre')->get();
            } elseif ($user->coordinacion_id) {
                $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)->orderBy('nombre')->get();
            } else {
                $grupos = Grupo::orderBy('nombre')->get();
            }
        } else {
            $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)->orderBy('nombre')->get();
        }

        $programacionesAgrupadas = $programaciones
            ->getCollection()
            ->groupBy(fn($p) => $p->grupo->nombre)
            ->map(fn($grupo) => $grupo->groupBy(fn($p) => $p->bloque_codigo ?? '—'));

        return view('admin.programaciones.index', compact('programaciones', 'coordinaciones', 'grupos', 'programacionesAgrupadas'));
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

    public function edit(Programacion $programacion)
    {
        $user = Auth::user();

        $grupos = Grupo::with('coordinacion')->orderBy('nombre')->get();
        $instructores = Instructor::where('activo', true)->orderBy('nombre')->get();
        $aulas = Aula::where('activa', true)->orderBy('nombre')->get();
        $feriados = Feriado::pluck('fecha')->map(fn ($f) => $f->format('Y-m-d'))->toArray();
        $cursos = $programacion->grupo->cursos()->get(['cursos.id', 'cursos.nombre', 'cursos.duracion_horas']);

        return view('admin.programaciones.edit', compact('programacion', 'grupos', 'instructores', 'aulas', 'feriados', 'cursos'));
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
        $validated = $request->validate([
            'inicio' => 'required|date_format:Y-m-d',
            'horas' => 'required|integer|min:1',
            'hora_inicio' => 'required|date_format:H:i',
        ]);

        try {
            $feriados = Feriado::pluck('fecha')->map(fn ($f) => $f->format('Y-m-d'))->toArray();

            $fechaInicio = Carbon::parse($validated['inicio'] . ' ' . $validated['hora_inicio']);
            $minutosTotales = $validated['horas'] * 50;

            if ($fechaInicio->format('H:i') < '08:30') {
                $fechaInicio->setTime(8, 30);
            } elseif ($fechaInicio->format('H:i') >= '12:00' && $fechaInicio->format('H:i') < '13:00') {
                $fechaInicio->setTime(13, 0);
            } elseif ($fechaInicio->format('H:i') >= '17:00') {
                $fechaInicio->addDay()->setTime(8, 30);
                while ($fechaInicio->isWeekend() || in_array($fechaInicio->format('Y-m-d'), $feriados)) {
                    $fechaInicio->addDay();
                }
            }

            $fechaActual = $fechaInicio->copy();
            $minutosAcumulados = 0;

            while ($minutosAcumulados < $minutosTotales) {
                if ($fechaActual->isWeekend() || in_array($fechaActual->format('Y-m-d'), $feriados)) {
                    $fechaActual->addDay()->setTime(8, 30);
                    continue;
                }

                $inicioManana = $fechaActual->copy()->setTime(8, 30);
                $finManana = $fechaActual->copy()->setTime(12, 0);
                $inicioTarde = $fechaActual->copy()->setTime(13, 0);
                $finTarde = $fechaActual->copy()->setTime(17, 0);

                // Mañana
                if ($fechaActual->lt($finManana)) {
                    if ($fechaActual->lt($inicioManana)) $fechaActual->setTime(8, 30);
                    $minDisp = $fechaActual->diffInMinutes($finManana);
                    $minUsar = min($minDisp, $minutosTotales - $minutosAcumulados);
                    $fechaActual->addMinutes($minUsar);
                    $minutosAcumulados += $minUsar;
                    if ($minutosAcumulados >= $minutosTotales) break;
                }

                // Almuerzo
                if ($fechaActual->format('H:i') >= '12:00' && $fechaActual->format('H:i') < '13:00') {
                    $fechaActual->setTime(13, 0);
                }

                // Tarde
                if ($fechaActual->lt($finTarde)) {
                    if ($fechaActual->lt($inicioTarde)) $fechaActual->setTime(13, 0);
                    $minDisp = $fechaActual->diffInMinutes($finTarde);
                    $minUsar = min($minDisp, $minutosTotales - $minutosAcumulados);
                    $fechaActual->addMinutes($minUsar);
                    $minutosAcumulados += $minUsar;
                    if ($minutosAcumulados >= $minutosTotales) break;
                }

                if ($minutosAcumulados < $minutosTotales) {
                    $fechaActual->addDay()->setTime(8, 30);
                }
            }

            return response()->json([
                'fecha_fin' => $fechaActual->format('Y-m-d'),
                'hora_fin' => $fechaActual->format('H:i'),
            ]);
        } catch (\Exception $e) {
            Log::error("Error al calcular fecha fin: " . $e->getMessage());
            return response()->json(['error' => 'Error al calcular la fecha de fin.'], 500);
        }
    }

    public function getDetalleDisponibilidadApi(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:instructor,aula',
            'id' => 'required|integer|min:1',
        ]);

        try {
            $query = Programacion::with(['curso:id,nombre', 'grupo:id,nombre,coordinacion_id', 'grupo.coordinacion:id,nombre,color'])
                ->where($validated['tipo'] . '_id', $validated['id'])
                ->orderBy('fecha_inicio')
                ->orderBy('hora_inicio');

            $eventos = [];
            $tabla = [];

            foreach ($query->get() as $p) {
                $color = $p->grupo?->coordinacion?->color ?? '#6B7280';
                $eventos[] = [
                    'title' => $p->curso->nombre,
                    'start' => $p->fecha_inicio->format('Y-m-d'),
                    'end' => $p->fecha_fin->gt($p->fecha_inicio) ? $p->fecha_fin->copy()->addDay()->format('Y-m-d') : null,
                    'allDay' => $p->fecha_fin->gt($p->fecha_inicio),
                    'color' => $color,
                    'extendedProps' => [
                        'grupo' => $p->grupo->nombre ?? 'N/A',
                        'coordinacion' => $p->grupo->coordinacion->nombre ?? 'N/A',
                        'fecha_inicio_fmt' => $p->fecha_inicio->format('d/m/Y'),
                        'fecha_fin_fmt' => $p->fecha_fin->format('d/m/Y'),
                        'hora_inicio_fmt' => $p->hora_inicio->format('H:i'),
                        'hora_fin_fmt' => $p->hora_fin->format('H:i')
                    ]
                ];

                $tabla[] = [
                    'fecha' => $p->fecha_inicio->eq($p->fecha_fin) ? $p->fecha_inicio->format('d/m/Y') : $p->fecha_inicio->format('d/m/Y') . ' - ' . $p->fecha_fin->format('d/m/Y'),
                    'hora_inicio' => $p->hora_inicio->format('H:i'),
                    'hora_fin' => $p->hora_fin->format('H:i'),
                    'curso' => $p->curso->nombre,
                    'coordinacion' => $p->grupo->coordinacion->nombre ?? 'N/A',
                    'color' => $color
                ];
            }

            return response()->json(['eventos' => $eventos, 'tabla' => $tabla]);

        } catch (\Exception $e) {
            Log::error("Error al obtener disponibilidad: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al consultar disponibilidad.'], 500);
        }
    }

    public function update(Request $request, Programacion $programacion)
    {
        $validated = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'curso_id' => 'required|exists:cursos,id',
            'fecha_inicio' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'fecha_fin' => 'required|date',
            'hora_fin' => 'required|date_format:H:i',
            'aula_id' => 'required|exists:aulas,id',
            'instructor_id' => 'nullable|exists:instructores,id',
            'bloque_codigo' => 'nullable|string|max:255',
        ]);

        $programacion->update($validated);

        activity()
            ->performedOn($programacion)
            ->causedBy(auth()->user())
            ->withProperties(['attributes' => $validated])
            ->log('Actualizó programación');

        return redirect()->route('admin.programaciones.index')
            ->with('success', 'Programación actualizada correctamente.');
    }

    public function getCursosPorGrupoApi(Grupo $grupo)
    {
        $user = Auth::user();

        if (!($user->esAdministrador() || $user->coordinacion_id === $grupo->coordinacion_id)) {
            return response()->json(['error' => 'No autorizado.'], 403);
        }

        try {
            $cursos = $grupo->cursos()->get(['cursos.id', 'cursos.nombre', 'cursos.duracion_horas']);
            return response()->json($cursos);
        } catch (\Exception $e) {
            Log::error("Error API cursos grupo: " . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    public function calcularFechaFinApi(Request $request)
    {
        $request->validate([
            'inicio' => 'required|date',
            'horas' => 'required|integer|min:1',
            'hora_inicio' => 'required|date_format:H:i',
        ]);

        try {
            $feriados = Feriado::pluck('fecha')->map(fn ($f) => $f->format('Y-m-d'))->toArray();
            $fechaInicio = Carbon::parse("{$request->inicio} {$request->hora_inicio}");

            // Ajuste inicial
            if ($fechaInicio->format('H:i') < '08:30') {
                $fechaInicio->setTime(8, 30);
            } elseif ($fechaInicio->between(Carbon::createFromTimeString('12:00'), Carbon::createFromTimeString('13:00'))) {
                $fechaInicio->setTime(13, 0);
            } elseif ($fechaInicio->format('H:i') >= '17:00') {
                $fechaInicio->addDay()->setTime(8, 30);
                while ($fechaInicio->isWeekend() || in_array($fechaInicio->format('Y-m-d'), $feriados)) {
                    $fechaInicio->addDay();
                }
            }

            $minutos = $request->horas * 50;
            $fechaActual = $fechaInicio->copy();

            while ($minutos > 0) {
                if ($fechaActual->isWeekend() || in_array($fechaActual->format('Y-m-d'), $feriados)) {
                    $fechaActual->addDay()->setTime(8, 30);
                    continue;
                }

                $bloques = [
                    ['08:30', '12:00'],
                    ['13:00', '17:00']
                ];

                foreach ($bloques as [$inicio, $fin]) {
                    $inicioBloque = $fechaActual->copy()->setTimeFromTimeString($inicio);
                    $finBloque = $fechaActual->copy()->setTimeFromTimeString($fin);

                    if ($fechaActual->gt($finBloque)) continue;
                    if ($fechaActual->lt($inicioBloque)) $fechaActual = $inicioBloque;

                    $disponible = $fechaActual->diffInMinutes($finBloque);
                    $usar = min($minutos, $disponible);

                    $fechaActual->addMinutes($usar);
                    $minutos -= $usar;

                    if ($minutos <= 0) break 2;
                }

                $fechaActual->addDay()->setTime(8, 30);
            }

            return response()->json([
                'fecha_fin' => $fechaActual->format('Y-m-d'),
                'hora_fin' => $fechaActual->format('H:i'),
            ]);

        } catch (\Exception $e) {
            Log::error("Error calcular fecha fin: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al calcular fecha'], 500);
        }
    }

    public function getDetalleDisponibilidadApi(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:instructor,aula',
            'id' => 'required|integer|min:1',
        ]);

        try {
            $tipo = $request->tipo;
            $id = $request->id;

            $ocupaciones = Programacion::with('curso', 'grupo.coordinacion')
                ->where("{$tipo}_id", $id)
                ->get();

            $eventos = [];
            $tabla = [];

            foreach ($ocupaciones as $oc) {
                $eventos[] = [
                    'title' => $oc->curso->nombre,
                    'start' => $oc->fecha_inicio->format('Y-m-d'),
                    'end' => $oc->fecha_fin->addDay()->format('Y-m-d'),
                    'allDay' => true,
                    'color' => $oc->grupo->coordinacion->color ?? '#999',
                    'extendedProps' => [
                        'grupo' => $oc->grupo->nombre ?? '',
                        'coordinacion' => $oc->grupo->coordinacion->nombre ?? '',
                        'fecha_inicio_fmt' => $oc->fecha_inicio->format('d/m/Y'),
                        'fecha_fin_fmt' => $oc->fecha_fin->format('d/m/Y'),
                        'hora_inicio_fmt' => $oc->hora_inicio->format('H:i'),
                        'hora_fin_fmt' => $oc->hora_fin->format('H:i'),
                    ]
                ];

                $tabla[] = [
                    'fecha' => $oc->fecha_inicio->format('d/m/Y') . ($oc->fecha_fin != $oc->fecha_inicio ? ' - ' . $oc->fecha_fin->format('d/m/Y') : ''),
                    'hora_inicio' => $oc->hora_inicio->format('H:i'),
                    'hora_fin' => $oc->hora_fin->format('H:i'),
                    'curso' => $oc->curso->nombre,
                    'coordinacion' => $oc->grupo->coordinacion->nombre ?? 'N/A',
                    'color' => $oc->grupo->coordinacion->color ?? '#999',
                ];
            }

            return response()->json(['eventos' => $eventos, 'tabla' => $tabla]);

        } catch (\Exception $e) {
            Log::error("Error disponibilidad detalle: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al obtener disponibilidad'], 500);
        }
    }

    public function showProgramarBloque()
    {
        return view('admin.programaciones.bloque');
    }
}
