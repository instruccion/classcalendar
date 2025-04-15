<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use Illuminate\Http\Request;

class AulaController extends Controller
{
    public function index()
    {
        $aulas = Aula::orderBy('nombre')->get();
        return view('admin.aulas.index', compact('aulas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:191',
            'lugar' => 'nullable|max:191',
            'capacidad' => 'nullable|integer|min:1',
        ]);

        Aula::updateOrCreate(
            ['id' => $request->input('id')],
            [
                'nombre' => $request->input('nombre'),
                'lugar' => $request->input('lugar'),
                'capacidad' => $request->input('capacidad'),
                'videobeam' => $request->boolean('videobeam'),
                'computadora' => $request->boolean('computadora'),
                'pizarra' => $request->boolean('pizarra'),
                'activa' => $request->boolean('activa'),
            ]
        );

        return redirect()->route('aulas.index')->with('success', 'Aula guardada correctamente.');
    }

    public function destroy(Aula $aula)
    {
        $aula->delete();
        return redirect()->route('aulas.index')->with('success', 'Aula eliminada correctamente.');
    }
}
