<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\Curso;

class ProgramacionBloqueController extends Controller
{
    public function index()
    {
        $grupos = Grupo::with('coordinacion')->orderBy('nombre')->get();
        return view('admin.programaciones.bloque.index', compact('grupos'));
    }

    public function getCursosPorGrupo(Request $request)
    {
        $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'tipo' => 'nullable|string'
        ]);

        $grupo = Grupo::findOrFail($request->grupo_id);
        $query = $grupo->cursos()->select('cursos.id', 'cursos.nombre');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        return response()->json($query->orderBy('nombre')->get());
    }

    public function ordenar(Request $request)
    {
        $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'cursos_id' => 'required|array',
            'cursos_id.*' => 'exists:cursos,id',
        ]);

        $grupo = Grupo::findOrFail($request->grupo_id);
        $cursos = Curso::whereIn('id', $request->cursos_id)->get();

        return view('admin.programaciones.bloque.ordenar', compact('grupo', 'cursos'));
    }
}
