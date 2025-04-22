<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\Curso;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Feriado;

class ProgramacionBloqueController extends Controller
{
    /**
     * Muestra la vista inicial para seleccionar el grupo y los cursos del bloque.
     */
    public function index()
    {
        $user = Auth::user();
        $grupos = [];

        // Lógica de permisos para obtener grupos
        if ($user->esAdministrador()) { // Asume que tienes este método en User.php
            $grupos = Grupo::with('coordinacion') // Cargar coordinación para mostrar nombre
                           ->orderBy('coordinacion_id') // Opcional: ordenar por coordinación
                           ->orderBy('nombre')
                           ->get();
        } elseif ($user->coordinacion_id) { // Asume que el usuario tiene una coordinación asignada
            $grupos = Grupo::where('coordinacion_id', $user->coordinacion_id)
                           ->with('coordinacion')
                           ->orderBy('nombre')
                           ->get();
        }
        // Añadir lógica para otros roles si es necesario

        return view('admin.programaciones.bloque.index', compact('grupos'));
    }

    /**
     * Obtiene los cursos disponibles para un grupo y tipo específico (para la API).
     * (Mantenemos tu nombre de método original)
     */
    public function getCursosPorGrupo(Request $request) // Cambiado nombre de parámetro a $request
    {
        $validated = $request->validate([ // Usar $validated para más seguridad
            'grupo_id' => 'required|exists:grupos,id',
            'tipo' => 'nullable|string'
        ]);

        $grupo = Grupo::find($validated['grupo_id']); // Usar find() es suficiente aquí
        $user = Auth::user();

        // Verificar permisos
        if (!$grupo || !($user->esAdministrador() || ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id))) {
            return response()->json(['error' => 'No autorizado o grupo no encontrado.'], 403);
        }

        try {
            $query = $grupo->cursos(); // Obtener la relación BelongsToMany

            if ($request->filled('tipo')) {
                $query->where('cursos.tipo', $validated['tipo']); // Filtrar por tipo si se proporciona
            }

            // Seleccionar solo id y nombre, ordenar
            $cursos = $query->orderBy('cursos.nombre')
                            ->get(['cursos.id', 'cursos.nombre']);

            return response()->json($cursos);

        } catch (\Exception $e) {
            Log::error("Error en getCursosPorGrupo API: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al obtener cursos.'], 500);
        }
    }

    /**
     * Muestra la vista para ordenar los cursos seleccionados.
     * Recibe los IDs de los cursos desde el formulario anterior.
     */
    public function ordenar(Request $request)
    {
        $validated = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'cursos_id' => 'required|array',
            'cursos_id.*' => 'integer|exists:cursos,id', // Validar cada ID en el array
        ]);

        $grupo = Grupo::find($validated['grupo_id']); // Usar find()
        $cursosIds = $validated['cursos_id'];
        $user = Auth::user();

        // Verificar permisos nuevamente por seguridad
        if (!$grupo || !($user->esAdministrador() || ($user->coordinacion_id && $user->coordinacion_id === $grupo->coordinacion_id))) {
             abort(403, 'No autorizado para este grupo.');
        }

        // Obtener los objetos Curso completos
        // findMany preserva el orden de los IDs pasados si la clave primaria es numérica
        $cursosSeleccionados = Curso::findMany($cursosIds);

        // Opcional: Si necesitas garantizar el orden exacto del array $cursosIds
        // $cursosOrdenados = collect($cursosIds)->map(function ($id) use ($cursosSeleccionados) {
        //        return $cursosSeleccionados->firstWhere('id', $id);
        //    })->filter(); // Filter para quitar nulos si algún ID no se encontró

        // Pasamos $cursosSeleccionados (el orden debería ser el de los IDs)
        $feriados = Feriado::pluck('fecha')->map(fn ($f) => $f->format('Y-m-d'))->toArray();
        return view('admin.programaciones.bloque.ordenar', compact('grupo', 'cursosSeleccionados', 'feriados'));    }

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
                        'fecha_fin' => $cursoData['fecha_inicio'], // Asumimos mismo día, ya viene con hora_fin
                        'hora_fin' => $cursoData['hora_fin'],
                        'aula_id' => null, // Aquí podrías mapear con ID si usas nombre de aula
                        'instructor_id' => null, // Igual para instructor
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
                return redirect()->route('admin.programaciones.index')->with('success', 'Programación por bloque guardada correctamente.');
            } catch (\Throwable $e) {
                DB::rollBack();
                report($e);
                return back()->with('error', 'Ocurrió un error al guardar la programación.');
            }
        }


}
