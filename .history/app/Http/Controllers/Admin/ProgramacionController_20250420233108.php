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

    public function update(Request $request, Programacion $programacion)
    {
        $validated = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'curso_id' => 'required|exists:cursos,id',
            'aula_id' => 'required|exists:aulas,id',
            'instructor_id' => 'nullable|exists:instructores,id',
            'fecha_inicio' => 'required|date',
            'hora_inicio' => 'required',
            'fecha_fin' => 'required|date',
            'hora_fin' => 'required',
            'bloque_codigo' => 'nullable|string|max:100',
        ]);

        $programacion->update($validated);

        activity()->causedBy(Auth::user())->performedOn($programacion)->log('Actualizó una programación');

        return redirect()->route('admin.programaciones.index')->with('success', 'Programación actualizada correctamente.');
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
