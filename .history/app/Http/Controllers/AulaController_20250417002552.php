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
            'nombre' => 'required|string|max:100',
            'lugar' => 'nullable|string|max:100',
            'capacidad' => 'nullable|integer|min:1',
            'videobeam' => 'nullable|boolean',
            'computadora' => 'nullable|boolean',
            'activa' => 'nullable|boolean',
        ]);

        $validated['videobeam'] = $request->has('videobeam');
        $validated['computadora'] = $request->has('computadora');
        $validated['activa'] = $request->has('activa');

        if ($request->id) {
            Aula::findOrFail($request->id)->update($validated);
            $mensaje = 'Aula actualizada correctamente.';
        } else {
            Aula::create($validated);
            $mensaje = 'Aula registrada correctamente.';
        }

        return redirect()->route('admin.aulas.index')
            ->with('toast', ['type' => 'success', 'message' => $mensaje]);
    }


    public function destroy(Aula $aula)
    {
        $aula->delete();

        return redirect()->route('admin.aulas.index')
            ->with('toast', ['type' => 'success', 'message' => 'Aula eliminada correctamente.']);
    }

}
