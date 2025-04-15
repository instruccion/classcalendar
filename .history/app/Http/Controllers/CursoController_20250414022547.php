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

        $grupoSeleccionadoId = $request->input('grupo_id');

        $grupos = Grupo::when($coordinacionId, fn($q) => $q->where('coordinacion_id', $coordinacionId))
            ->orderBy('nombre')->get();

        $coordinaciones = $usuario->rol === 'administrador'
            ? Coordinacion::orderBy('nombre')->get()
            : collect(); // Vacío si no es admin

        $cursos = Curso::with('grupos')
            ->when($grupoSeleccionadoId, function ($query) use ($grupoSeleccionadoId) {
                $query->whereHas('grupos', fn($q) => $q->where('grupo_id', $grupoSeleccionadoId));
            })
            ->when($coordinacionId, function ($query) use ($coordinacionId) {
                $query->whereHas('grupos', fn($q) => $q->where('coordinacion_id', $coordinacionId));
            })
            ->orderBy('nombre')
            ->get();

        return view('admin.cursos.index', compact(
            'usuario', 'grupos', 'cursos', 'coordinaciones', 'coordinacionId'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|string',
            'descripcion' => 'nullable|string',
            'duracion_horas' => 'required|integer|min:1',
            'grupo_ids' => 'required|array',
            'grupo_ids.*' => 'exists:grupos,id',
        ]);

        $curso = Curso::create([
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'duracion_horas' => $validated['duracion_horas'],
        ]);

        $curso->grupos()->sync($validated['grupo_ids']);

        registrar_auditoria("Curso creado", "Se registró el curso: {$curso->nombre}");

        return redirect()->route('cursos.index')->with('success', 'Curso registrado correctamente.');
    }

    public function edit(Curso $curso)
    {
        $usuario = Auth::user();
        $coordinacionId = $usuario->rol === 'administrador' ? null : $usuario->coordinacion_id;

        $grupos = Grupo::when($coordinacionId, function ($query) use ($coordinacionId) {
                return $query->where('coordinacion_id', $coordinacionId);
            })
            ->orderBy('nombre')
            ->get();

        $grupoIds = $curso->grupos()->pluck('grupos.id')->toArray();

        return view('admin.cursos.edit', compact('curso', 'grupos', 'grupoIds'));
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

        $curso->update([
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'duracion_horas' => $validated['duracion_horas'],
        ]);

        $curso->grupos()->sync($validated['grupo_ids']);

        registrar_auditoria("Curso actualizado", "Se actualizó el curso: {$curso->nombre}");

        return redirect()->route('cursos.index')->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Curso $curso)
    {
        registrar_auditoria("Curso eliminado", "Se eliminó el curso: {$curso->nombre}");

        $curso->grupos()->detach();
        $curso->delete();

        return redirect()->route('cursos.index')->with('success', 'Curso eliminado correctamente.');
    }
}
