<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrupoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $coordinacionId = $usuario->rol === 'administrador'
            ? $request->input('coordinacion_id')
            : $usuario->coordinacion_id;

        $grupos = Grupo::with('coordinacion')
            ->when($coordinacionId, fn($q) => $q->where('coordinacion_id', $coordinacionId))
            ->orderBy('nombre')
            ->get();

        $coordinaciones = Coordinacion::orderBy('nombre')->get();

        return view('admin.grupos.index', compact('grupos', 'coordinaciones', 'usuario', 'coordinacionId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:191',
            'descripcion' => 'nullable|string',
            'coordinacion_id' => 'required|exists:coordinaciones,id',
        ]);

        $grupo = Grupo::create($validated);

        registrar_auditoria("Grupo creado", "Se creó el grupo: {$grupo->nombre}");

        return redirect()->route('admin.grupos.index')->with('success', 'Grupo registrado correctamente.');
    }

    public function edit(Grupo $grupo)
    {
        $coordinaciones = Coordinacion::orderBy('nombre')->get();
        return view('admin.grupos.edit', compact('grupo', 'coordinaciones'));
    }

    public function update(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:191',
            'descripcion' => 'nullable|string',
            'coordinacion_id' => 'required|exists:coordinaciones,id',
        ]);

        $grupo->update($validated);

        registrar_auditoria("Grupo actualizado", "Se actualizó el grupo: {$grupo->nombre}");

        return redirect()->route('admin.grupos.index')->with('success', 'Grupo actualizado correctamente.');
    }

    public function destroy($id)
    {
        $grupo = Grupo::findOrFail($id);
        $grupo->delete();

        registrar_auditoria("Grupo eliminado", "Se eliminó el grupo con ID: {$id}");

        return redirect()->route('admin.grupos.index')->with('success', 'Grupo eliminado con éxito.');
    }

    // ✅ Este método permite actualizar el dropdown de grupos desde el filtro de coordinación
    public function getGruposByCoordinacion(Request $request)
    {
        $coordinacionId = $request->input('coordinacion_id');

        $grupos = Grupo::where('coordinacion_id', $coordinacionId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json(['grupos' => $grupos]);
    }
}
