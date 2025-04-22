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

    // El método store y update vendrán en las siguientes fases

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

        // Verificación de acceso a grupo
        $grupo = Grupo::with('coordinacion')->findOrFail($validated['grupo_id']);
        if (!($user->esAdministrador() || $user->coordinacion_id === $grupo->coordinacion_id)) {
            return back()->with('error', 'No tienes permiso para asignar programaciones a este grupo.');
        }

        // Verificar conflictos de instructor (si aplica)
        if ($validated['instructor_id']) {
            $conflictoInstructor = Programacion::where('instructor_id', $validated['instructor_id'])
                ->where(function ($q) use ($validated) {
                    $q->whereRaw('TIMESTAMP(fecha_inicio, hora_inicio) < ?', [$validated['fecha_fin'] . ' ' . $validated['hora_fin']])
                        ->whereRaw('TIMESTAMP(fecha_fin, hora_fin) > ?', [$validated['fecha_inicio'] . ' ' . $validated['hora_inicio']]);
                })->exists();

            if ($conflictoInstructor) {
                return back()->with('error', 'El instructor seleccionado ya está asignado en ese horario.')->withInput();
            }
        }

        // Verificar conflictos de aula
        $conflictoAula = Programacion::where('aula_id', $validated['aula_id'])
            ->where(function ($q) use ($validated) {
                $q->whereRaw('TIMESTAMP(fecha_inicio, hora_inicio) < ?', [$validated['fecha_fin'] . ' ' . $validated['hora_fin']])
                    ->whereRaw('TIMESTAMP(fecha_fin, hora_fin) > ?', [$validated['fecha_inicio'] . ' ' . $validated['hora_inicio']]);
            })->exists();

        if ($conflictoAula) {
            return back()->with('error', 'El aula seleccionada ya está ocupada en ese horario.')->withInput();
        }

        try {
            $programacion = new Programacion();
            $programacion->curso_id = $validated['curso_id'];
            $programacion->grupo_id = $validated['grupo_id'];
            $programacion->instructor_id = $validated['instructor_id'] ?? null;
            $programacion->aula_id = $validated['aula_id'];
            $programacion->fecha_inicio = $validated['fecha_inicio'];
            $programacion->fecha_fin = $validated['fecha_fin'];
            $programacion->hora_inicio = $validated['hora_inicio'];
            $programacion->hora_fin = $validated['hora_fin'];
            $programacion->estado_instructor = $validated['estado_instructor'] ?? 'programado';
            $programacion->notificado_inac = $request->has('notificado_inac');
            $programacion->fecha_notificacion_inac = $validated['fecha_notificacion_inac'] ?? null;
            $programacion->save();

            activity()
                ->causedBy($user)
                ->performedOn($programacion)
                ->log('Programación creada');

            return redirect()->route('admin.programaciones.index')
                ->with('success', 'Programación registrada correctamente.');
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

        // Verificación de acceso al grupo
        $grupo = Grupo::with('coordinacion')->findOrFail($validated['grupo_id']);
        if (!($user->esAdministrador() || $user->coordinacion_id === $grupo->coordinacion_id)) {
            return back()->with('error', 'No tienes permiso para modificar este grupo.')->withInput();
        }

        // Validar disponibilidad del instructor (si se cambió)
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

        // Validar disponibilidad del aula (si se cambió)
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
            $programacion->curso_id = $validated['curso_id'];
            $programacion->grupo_id = $validated['grupo_id'];
            $programacion->instructor_id = $validated['instructor_id'] ?? null;
            $programacion->aula_id = $validated['aula_id'];
            $programacion->fecha_inicio = $validated['fecha_inicio'];
            $programacion->fecha_fin = $validated['fecha_fin'];
            $programacion->hora_inicio = $validated['hora_inicio'];
            $programacion->hora_fin = $validated['hora_fin'];
            $programacion->estado_instructor = $validated['estado_instructor'] ?? 'programado';
            $programacion->notificado_inac = $request->has('notificado_inac');
            $programacion->fecha_notificacion_inac = $validated['fecha_notificacion_inac'] ?? null;
            $programacion->save();

            activity()
                ->causedBy($user)
                ->performedOn($programacion)
                ->log('Programación actualizada');

            return redirect()->route('admin.programaciones.index')
                ->with('success', 'Programación actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al actualizar programación ID {$programacion->id}: {$e->getMessage()}");
            return back()->with('error', 'Error al actualizar la programación.')->withInput();
        }
    }
