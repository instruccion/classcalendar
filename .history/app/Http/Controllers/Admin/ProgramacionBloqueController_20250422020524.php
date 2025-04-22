<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\Curso;
use App\Models\Feriado;
use App\Models\Programacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProgramacionBloqueController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $grupos = [];

        if ($user->esAdministrador()) {
            $grupos = Grupo::with('coordinacion')
                ->orderBy('coordinacion_id')
                ->orderBy('nombre')
                ->get();
        } elseif ($user->coordinacion_id) {
            $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)
                ->with('coordinacion')
                ->orderBy('nombre')
                ->get();
        }

        return view('admin.programaciones.bloque.index', compact('grupos'));
    }

    public function getCursosPorGrupo(Request $request)
    {
        $validated = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'tipo' => 'nullable|string'
        ]);

        $grupo = Grupo::find($validated['grupo_id']);
        $user = Auth::user();

        if (!$grupo || !($user->esAdministrador() || ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id))) {
            return response()->json(['error' => 'No autorizado o grupo no encontrado.'], 403);
        }

        try {
            $query = $grupo->cursos();

            if ($request->filled('tipo')) {
                $query->where('cursos.tipo', $validated['tipo']);
            }

            $cursos = $query->orderBy('cursos.nombre')
                ->get(['cursos.id', 'cursos.nombre', 'cursos.duracion_horas']);

            return response()->json($cursos);
        } catch (\Exception $e) {
            Log::error("Error en getCursosPorGrupo API: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al obtener cursos.'], 500);
        }
    }

    public function ordenar(Request $request)
    {
        $validated = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'cursos_id' => 'required|array',
            'cursos_id.*' => 'integer|exists:cursos,id',
        ]);

        $grupo = Grupo::find($validated['grupo_id']);
        $cursosIds = $validated['cursos_id'];
        $user = Auth::user();

        if (!$grupo || !($user->esAdministrador() || ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id))) {
            abort(403, 'No autorizado para este grupo.');
        }

        $cursosSeleccionados = Curso::whereIn('id', $cursosIds)
            ->select('id', 'nombre', 'duracion_horas')
            ->orderByRaw("FIELD(id, " . implode(',', $cursosIds) . ")")
            ->get();

        $feriados = Feriado::pluck('fecha')->map(fn($f) => $f->format('Y-m-d'))->toArray();

        return view('admin.programaciones.bloque.ordenar', compact('grupo', 'cursosSeleccionados', 'feriados'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'bloque_codigo' => 'nullable|string|max:100',
            'cursos' => 'required|array|min:1',
            'cursos.*.id' => 'required|exists:cursos,id',
            'cursos.*.fecha_inicio' => 'required|date',
            'cursos.*.hora_inicio' => 'required|date_format:H:i',
            'cursos.*.hora_fin' => 'required|date_format:H:i',
            'cursos.*.aula' => 'nullable|string|max:100',
            'cursos.*.instructor' => 'nullable|string|max:100',
        ]);

        $grupoId = $validated['grupo_id'];
        $bloqueCodigo = $validated['bloque_codigo'] ?? null;

        try {
            DB::beginTransaction();

            foreach ($validated['cursos'] as $cursoData) {
                Programacion::create([
                    'grupo_id' => $grupoId,
                    'curso_id' => $cursoData['id'],
                    'fecha_inicio' => $cursoData['fecha_inicio'],
                    'hora_inicio' => $cursoData['hora_inicio'],
                    'fecha_fin' => $cursoData['fecha_fin'] ?? $cursoData['fecha_inicio'],
                    'hora_fin' => $cursoData['hora_fin'],
                    'aula_id' => null,
                    'instructor_id' => null,
                    'bloque_codigo' => $bloqueCodigo,
                    'observaciones' => 'Programado por bloque',
                ]);
            }

            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'grupo_id' => $grupoId,
                    'cantidad' => count($validated['cursos']),
                    'bloque_codigo' => $bloqueCodigo,
                ])
                ->log('Programación por bloque');

            DB::commit();

            return redirect()->route('admin.programaciones.index')
                ->with('success', 'Programación por bloque guardada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Ocurrió un error al guardar la programación.');
        }
    }
}
