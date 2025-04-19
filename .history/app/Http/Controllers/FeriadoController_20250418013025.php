<?php

namespace App\Http\Controllers;

use App\Models\Feriado;
use Illuminate\Http\Request;

class FeriadoController extends Controller
{
    public function index()
    {
        $feriados = Feriado::orderBy('fecha')->get();
        return view('admin.feriados.index', compact('feriados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:191',
            'fecha' => 'required|date',
            'recurrente' => 'boolean'
        ]);

        Feriado::create($request->only('titulo', 'fecha', 'recurrente'));

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Feriado registrado correctamente.',
        ]);
    }

    public function destroy(Feriado $feriado)
    {
        $feriado->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Feriado eliminado.',
        ]);
    }

    public function update(Request $request, Feriado $feriado)
    {
        $request->validate([
            'titulo' => 'required|string|max:191',
            'fecha' => 'required|date',
            'recurrente' => 'boolean',
        ]);

        $feriado->update([
            'titulo' => $request->titulo,
            'fecha' => $request->fecha,
            'recurrente' => $request->has('recurrente'),
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Feriado actualizado correctamente.',
        ]);
    }

}
