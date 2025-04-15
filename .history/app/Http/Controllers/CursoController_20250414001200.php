<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



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
        $request->validate([
            'nombre' => 'required|string|max:191',
            'tipo' => 'required|in:Inicial,Periódico,General',
            'duracion_horas' => 'required|integer|min:1',
            'grupo_ids' => 'required|array|min:1',
            'grupo_ids.*' => 'exists:grupos,id',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $curso = Curso::create([
            'nombre' => $request->nombre,
            'tipo' => strtolower($request->tipo), // En base a tu estructura enum
            'duracion_horas' => $request->duracion_horas,
            'descripcion' => $request->descripcion,
            'coordinacion_id' => Auth::user()->coordinacion_id, // si aplica
        ]);

        $curso->grupos()->attach($request->grupo_ids);

        return redirect()->route('cursos.index')->with('success', 'Curso registrado exitosamente.');
    }

    public function update(Request $request, Curso $curso)
    {
        $request->validate([
            'nombre' => 'required|string|max:191|unique:cursos,nombre,' . $curso->id,
            'tipo' => 'required|in:inicial,recurrente,puntual',
            'duracion_horas' => 'required|integer|min:1',
            'descripcion' => 'nullable|string',
            'grupo_ids' => 'required|array|min:1',
            'grupo_ids.*' => 'exists:grupos,id',
        ]);

        $curso->update([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'duracion_horas' => $request->duracion_horas,
            'descripcion' => $request->descripcion,
        ]);

        $curso->grupos()->sync($request->grupo_ids);

        return redirect()->route('cursos.index')->with('success', 'Curso actualizado correctamente.');
    }

}
