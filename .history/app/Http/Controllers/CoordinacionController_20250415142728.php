<?php

namespace App\Http\Controllers;

use App\Models\Coordinacion;
use App\Models\ColorDisponible;
use Illuminate\Http\Request;

class CoordinacionController extends Controller
{
    public function index()
    {
        $coordinaciones = Coordinacion::all();
        return view('admin.coordinaciones.index', compact('coordinaciones'));
    }



    public function create()
    {
        $colores = ColorDisponible::where('disponible', true)->orderBy('color')->get();
        return view('admin.coordinaciones.create', compact('colores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:191|unique:coordinaciones,nombre',
            'color' => 'required|exists:colores_disponibles,color',
        ]);

        Coordinacion::create($request->only('nombre', 'descripcion', 'color') + ['activa' => true]);

        ColorDisponible::where('color', $request->color)->update(['disponible' => false]);

        return redirect()->route('coordinaciones.index')->with('success', 'Coordinación registrada exitosamente.');
    }

    public function edit(Coordinacion $coordinacion)
    {
        $colores = ColorDisponible::where(function ($q) use ($coordinacion) {
            $q->where('disponible', true)
              ->orWhere('color', $coordinacion->color);
        })->orderBy('color')->get();

        return view('admin.coordinaciones.edit', compact('coordinacion', 'colores'));
    }

    public function update(Request $request, Coordinacion $coordinacion)
    {
        $request->validate([
            'nombre' => 'required|string|max:191|unique:coordinaciones,nombre,' . $coordinacion->id,
            'color' => 'required|exists:colores_disponibles,color',
        ]);

        if ($coordinacion->color !== $request->color) {
            ColorDisponible::where('color', $coordinacion->color)->update(['disponible' => true]);
            ColorDisponible::where('color', $request->color)->update(['disponible' => false]);
        }

        $coordinacion->update($request->only('nombre', 'descripcion', 'color'));

        return redirect()->route('coordinaciones.index')->with('success', 'Coordinación actualizada.');
    }

    // Método destroy en CoordinacionController
    public function destroy(Coordinacion $coordinacion)
    {
        // Verificar si la coordinación está en uso o si no hay restricciones
        $coordinacion->delete();

        return redirect()->route('admin.coordinaciones.index')->with('success', 'Coordinación eliminada correctamente.');
    }


}
