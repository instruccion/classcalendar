<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aula;

class AulaController extends Controller
{
    public function index()
    {
        $aulas = Aula::orderBy('nombre')->get();
        return view('admin.aulas.index', compact('aulas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:191',
            'lugar' => 'nullable|string|max:191',
            'capacidad' => 'nullable|integer|min:1',
            'videobeam' => 'nullable|boolean',
            'computadora' => 'nullable|boolean',
            'activa' => 'nullable|boolean',
        ]);

        Aula::create([
            'nombre' => $validated['nombre'],
            'lugar' => $validated['lugar'] ?? null,
            'capacidad' => $validated['capacidad'] ?? null,
            'videobeam' => $request->has('videobeam'),
            'computadora' => $request->has('computadora'),
            'activa' => $request->has('activa'),
        ]);

        return redirect()->route('aulas.index')->with('success', 'Aula registrada correctamente');
    }

    public function destroy(Aula $aula)
    {
        $aula->delete();
        return redirect()->route('aulas.index')->with('success', 'Aula eliminada correctamente');
    }
}
