<?php

namespace App\Http\Controllers;

use App\Models\Coordinacion;
use App\Models\ColorDisponible;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoordinacionController extends Controller
{
    public function index()
    {
        $coordinaciones = Coordinacion::orderBy('nombre')->get();
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
            'nombre' => 'required|string|max:191',
            'color' => 'required|exists:colores_disponibles,color',
        ]);

        DB::transaction(function () use ($request) {
            Coordinacion::create([
                'nombre' => $request->nombre,
                'color' => $request->color,
                'activa' => true,
            ]);

            ColorDisponible::where('color', $request->color)->update(['disponible' => false]);
        });

        return redirect()->route('coordinaciones.index')->with('exito', 'Coordinación registrada correctamente.');
    }

    public function edit(Coordinacion $coordinacion)
    {
        $colores = ColorDisponible::where(function($query) use ($coordinacion) {
            $query->where('disponible', true)
                  ->orWhere('color', $coordinacion->color);
        })->orderBy('color')->get();

        return view('admin.coordinaciones.edit', compact('coordinacion', 'colores'));
    }

    public function update(Request $request, Coordinacion $coordinacion)
    {
        $request->validate([
            'nombre' => 'required|string|max:191',
            'color' => 'required|exists:colores_disponibles,color',
        ]);

        DB::transaction(function () use ($request, $coordinacion) {
            if ($coordinacion->color !== $request->color) {
                ColorDisponible::where('color', $coordinacion->color)->update(['disponible' => true]);
                ColorDisponible::where('color', $request->color)->update(['disponible' => false]);
            }

            $coordinacion->update([
                'nombre' => $request->nombre,
                'color' => $request->color,
            ]);
        });

        return redirect()->route('coordinaciones.index')->with('exito', 'Coordinación actualizada correctamente.');
    }

    public function destroy(Coordinacion $coordinacion)
    {
        DB::transaction(function () use ($coordinacion) {
            ColorDisponible::where('color', $coordinacion->color)->update(['disponible' => true]);
            $coordinacion->delete();
        });

        return redirect()->route('coordinaciones.index')->with('exito', 'Coordinación eliminada correctamente.');
    }
}
