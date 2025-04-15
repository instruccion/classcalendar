<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Grupo as GrupoModel;



class CursoController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $coordinacionId = $usuario->rol === 'administrador' ? null : $usuario->coordinacion_id;

        $grupos = Grupo::when($coordinacionId, function ($q) use ($coordinacionId) {
            $q->where('coordinacion_id', $coordinacionId);
        })->orderBy('nombre')->get();

        return view('admin.cursos.index', [
            'usuario' => $usuario,
            'coordinaciones' => [], // Opcional si quieres usarlo luego
            'grupos' => $grupos,
            'coordinacionId' => $coordinacionId
        ]);
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
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

        return redirect()->route('cursos.index')->with('success', 'Curso creado exitosamente.');
    }


    public function update(Request $request, Curso $curso)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|string',
            'descripcion' => 'nullable|string',
            'duracion_horas' => 'required|integer|min:1',
            'grupo_ids' => 'required|array',
            'grupo_ids.*' => 'exists:grupos,id',
        ]);

        // Actualizar los datos del curso
        $curso->update([
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'duracion_horas' => $validated['duracion_horas'],
        ]);

        // Actualizar las asociaciones con grupos
        $curso->grupos()->sync($validated['grupo_ids']);

        return redirect()->route('cursos.index')->with('success', 'Curso actualizado exitosamente.');
    }


}
