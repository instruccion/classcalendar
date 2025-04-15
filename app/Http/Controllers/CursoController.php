<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $coordinacionId = $usuario->rol === 'administrador' ? null : $usuario->coordinacion_id;

        // Obtener coordinaciones solo si el usuario es administrador
        $coordinaciones = $usuario->rol === 'administrador' ? Coordinacion::all() : [];

        // Filtro de grupos según la coordinación
        $grupos = Grupo::when($coordinacionId, function ($q) use ($coordinacionId) {
            $q->where('coordinacion_id', $coordinacionId);
        })->orderBy('nombre')->get();

        // Filtrar los cursos según el grupo seleccionado
        $grupoSeleccionadoId = $request->input('grupo_id');
        $cursos = Curso::with('grupos')
            ->when($grupoSeleccionadoId, function ($query) use ($grupoSeleccionadoId) {
                $query->whereHas('grupos', function ($q) use ($grupoSeleccionadoId) {
                    $q->where('grupo_id', $grupoSeleccionadoId);
                });
            })
            ->when($coordinacionId, function ($query) use ($coordinacionId) {
                $query->whereHas('grupos', function ($q) use ($coordinacionId) {
                    $q->where('coordinacion_id', $coordinacionId);
                });
            })
            ->orderBy('nombre')
            ->get();

        return view('admin.cursos.index', [
            'usuario' => $usuario,
            'coordinaciones' => $coordinaciones,
            'grupos' => $grupos,
            'cursos' => $cursos,
            'coordinacionId' => $coordinacionId
        ]);
    }

    // Método para guardar los cambios en el curso
    public function store(Request $request)
    {
        // Validación de datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|string',
            'descripcion' => 'nullable|string',
            'duracion_horas' => 'required|integer|min:1',
            'grupo_ids' => 'required|array',
            'grupo_ids.*' => 'exists:grupos,id',
        ]);

        // Crear el curso
        $curso = Curso::create([
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'duracion_horas' => $validated['duracion_horas'],
        ]);

        // Asociar los grupos seleccionados al curso
        $curso->grupos()->sync($validated['grupo_ids']);

        // Auditoría
        registrar_auditoria("Curso creado", "Se registró el curso: {$curso->nombre}");

        return redirect()->route('admin.cursos.index')->with('success', 'Curso creado exitosamente.');
    }


    public function update(Request $request, Curso $curso)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|string',
            'descripcion' => 'nullable|string',
            'duracion_horas' => 'required|integer|min:1',
            'grupo_ids' => 'required|array',
            'grupo_ids.*' => 'exists:grupos,id',
        ]);

        // Actualizar el curso
        $curso->update([
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'duracion_horas' => $validated['duracion_horas'],
        ]);

        // Actualizar las asociaciones con grupos
        $curso->grupos()->sync($validated['grupo_ids']);

        // Auditoría
        registrar_auditoria("Curso actualizado", "Se modificó el curso: {$curso->nombre}");

        return redirect()->route('cursos.index')->with('success', 'Curso actualizado exitosamente.');
    }
}
