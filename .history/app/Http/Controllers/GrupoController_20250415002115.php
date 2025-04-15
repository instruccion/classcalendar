<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Coordinacion;
use App\Models\Curso;  // Asegúrate de que el modelo Curso esté importado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrupoController extends Controller
{
    // Método index para filtrar los grupos y cursos
    public function index(Request $request)
{
    $usuario = Auth::user();
    // Si el usuario no es administrador, se filtra por su coordinacion
    $coordinacionId = $usuario->rol === 'administrador' ? $request->coordinacion_id : $usuario->coordinacion_id;

    // Obtener los grupos filtrados por la coordinacion
    $grupos = Grupo::with('coordinacion')
        ->when($coordinacionId, fn($q) => $q->where('coordinacion_id', $coordinacionId))
        ->orderBy('nombre')
        ->get();

    // Pasar las coordinaciones al filtro
    $coordinaciones = Coordinacion::orderBy('nombre')->get();

    // Pasar las variables necesarias a la vista
    return view('admin.grupos.index', compact('grupos', 'coordinaciones', 'usuario', 'coordinacionId'));
}




    public function store(Request $request)
    {
        // Validación de los datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:191',
            'descripcion' => 'nullable|string',
            'coordinacion_id' => 'required|exists:coordinaciones,id',
        ]);

        // Crear el nuevo grupo
        $grupo = Grupo::create($validated);

        // Registrar auditoría
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
        // Validación de los datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:191',
            'descripcion' => 'nullable|string',
            'coordinacion_id' => 'required|exists:coordinaciones,id',
        ]);

        // Actualizar el grupo
        $grupo->update($validated);

        // Registrar auditoría
        registrar_auditoria("Grupo actualizado", "Se actualizó el grupo: {$grupo->nombre}");

        return redirect()->route('grupos.index')->with('success', 'Grupo actualizado correctamente.');
    }

    public function destroy($id)
    {
        // Eliminar el grupo
        $grupo = Grupo::findOrFail($id);
        $grupo->delete();

        return redirect()->route('grupos.index')->with('success', 'Grupo eliminado con éxito');
    }

    public function getGruposByCoordinacion(Request $request)
    {
        $coordinacionId = $request->input('coordinacion_id');

        // Obtener los grupos relacionados con la coordinación seleccionada
        $grupos = Grupo::where('coordinacion_id', $coordinacionId)->get();

        // Devolver los grupos en formato JSON
        return response()->json(['grupos' => $grupos]);
    }

}
