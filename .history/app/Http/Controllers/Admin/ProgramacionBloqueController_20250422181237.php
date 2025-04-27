<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Grupo, Curso, Feriado, Programacion, Aula, Instructor};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

    public function editBloque(Request $request, Grupo $grupo, $bloque_codigo = null)
    {
        $user = Auth::user();
        // Verificar permisos para este grupo
        if (!$grupo || !($user->esAdministrador() || ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id))) {
            abort(403, 'No autorizado para editar bloques de este grupo.');
        }

        // Convertir '_sin_codigo_' de nuevo a null para la consulta DB
        $codigoBusqueda = ($bloque_codigo === '_sin_codigo_') ? null : $bloque_codigo;

        // Buscar programaciones existentes para este bloque y grupo
        $query = Programacion::where('grupo_id', $grupo->id)
                            ->with('curso:id,nombre,duracion_horas'); // Cargar datos del curso

        if ($codigoBusqueda === null) {
             $query->whereNull('bloque_codigo');
        } else {
             $query->where('bloque_codigo', $codigoBusqueda);
        }

        // Ordenar por fecha/hora inicio para obtener el orden guardado
        $programacionesExistentes = $query->orderBy('fecha_inicio', 'asc')
                                          ->orderBy('hora_inicio', 'asc')
                                          ->get();

        // Si no se encontraron programaciones para ese bloque/grupo
        if ($programacionesExistentes->isEmpty()) {
             // Redirigir a la selección de bloques con un mensaje
             return redirect()->route('admin.programaciones.bloque.index', ['grupo_id' => $grupo->id])
                            ->with('error', 'No se encontraron programaciones para editar con ese código de bloque.');
        }

        // Extraer datos necesarios para la vista Alpine
        // Mapeamos los datos necesarios para el script ordenarBloque
        $cursosParaVista = $programacionesExistentes->map(fn($p) => [
            'id' => $p->curso->id, // ID del Curso
            'nombre' => $p->curso->nombre,
            'duracion_horas' => $p->curso->duracion_horas,
            // Formatear fechas y horas para los inputs y el script
            'fecha_inicio' => $p->fecha_inicio ? Carbon::parse($p->fecha_inicio)->format('Y-m-d') : '',
            'hora_inicio' => $p->hora_inicio ? Carbon::parse($p->hora_inicio)->format('H:i') : '', // Asume que hora_inicio es TIME o DATETIME
            'fecha_fin' => $p->fecha_fin ? Carbon::parse($p->fecha_fin)->format('Y-m-d') : '',
            'hora_fin' => $p->hora_fin ? Carbon::parse($p->hora_fin)->format('H:i') : '', // Asume que hora_fin es TIME o DATETIME
            'programacion_id' => $p->id, // ID de la programación original (podría ser útil)
            'modificado' => false // Empezar como no modificado
        ]);

        // Obtener datos comunes del primer curso (aula, instructor, fechas)
        $primeraProg = $programacionesExistentes->first();
        $fechaInicioActual = $primeraProg->fecha_inicio ? $primeraProg->fecha_inicio->format('Y-m-d') : '';
        $horaInicioActual = $primeraProg->hora_inicio ? Carbon::parse($primeraProg->hora_inicio)->format('H:i') : '08:30'; // Usar Carbon si es TIME
        $aulaActualId = $primeraProg->aula_id;
        $instructorActualId = $primeraProg->instructor_id;

        // Datos para los selects del formulario de edición
        $aulas = Aula::where('activa', true)->orderBy('nombre')->get(['id', 'nombre', 'lugar']);
        $instructores = Instructor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
        $feriados = Feriado::pluck('fecha')->map(fn ($f) => Carbon::parse($f)->format('Y-m-d'))->toArray(); // Asegurar formato

        // Devolver la vista de edición con todos los datos necesarios
        return view('admin.programaciones.bloque.edit', compact(
            'grupo',
            'bloque_codigo',         // Código original o '_sin_codigo_' para la URL de update
            'cursosParaVista',       // Array de cursos para Alpine
            'aulas',                 // Para el select de Aula
            'instructores',          // Para el select de Instructor
            'feriados',              // Para el cálculo JS (aunque ahora es backend)
            'fechaInicioActual',     // Para pre-rellenar input fecha
            'horaInicioActual',      // Para pre-rellenar input hora
            'aulaActualId',          // Para pre-seleccionar aula
            'instructorActualId'     // Para pre-seleccionar instructor
        ));
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
