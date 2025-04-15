<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Coordinacion;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $coordinacionId = $usuario->rol === 'administrador' ? null : $usuario->coordinacion_id;

        $coordinaciones = $usuario->rol === 'administrador'
            ? Coordinacion::orderBy('nombre')->get()
            : collect(); // vacío para no admin

        $grupos = Grupo::when($coordinacionId, fn($q) => $q->where('coordinacion_id', $coordinacionId))
                    ->orderBy('nombre')->get();

        $grupoSeleccionadoId = $request->input('grupo_id');

        $cursos = Curso::with('grupos')
            ->when($grupoSeleccionadoId, fn($q) => $q->whereHas('grupos', fn($q2) => $q2->where('grupo_id', $grupoSeleccionadoId)))
            ->when($coordinacionId, fn($q) => $q->whereHas('grupos', fn($q2) => $q2->where('coordinacion_id', $coordinacionId)))
            ->orderBy('nombre')->get();

        return view('admin.cursos.index', compact('usuario', 'grupos', 'cursos', 'coordinacionId', 'coordinaciones'));
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

        return redirect()->route('cursos.index')->with('success', 'Curso creado exitosamente.');
    }

    public function edit(Curso $curso)
    {
        $usuario = Auth::user();
        $coordinacionId = $usuario->rol === 'administrador' ? null : $usuario->coordinacion_id;

        $grupos = Grupo::when($coordinacionId, function ($q) use ($coordinacionId) {
            $q->where('coordinacion_id', $coordinacionId);
        })->orderBy('nombre')->get();

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

        registrar_auditoria("Curso actualizado", "Se modificó el curso: {$curso->nombre}");

        return redirect()->route('cursos.index')->with('success', 'Curso actualizado exitosamente.');
    }


    public function destroy(Curso $curso)
    {
        $nombre = $curso->nombre;

        // Verifica si el curso tiene cursos programados asociados (opcional)
        if ($curso->cursosProgramados()->count() > 0) {
            return redirect()->route('cursos.index')
                ->with('error', "El curso '{$nombre}' no se puede eliminar porque tiene programaciones asociadas.");
        }

        // Elimina las relaciones con grupos
        $curso->grupos()->detach();

        // Elimina el curso
        $curso->delete();

        // Auditoría
        registrar_auditoria("Curso eliminado", "Se eliminó el curso: {$nombre}");

        return redirect()->route('cursos.index')->with('success', 'Curso eliminado exitosamente.');
    }

}
