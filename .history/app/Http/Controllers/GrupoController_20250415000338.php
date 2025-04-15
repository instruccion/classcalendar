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

        // Verifica si el usuario es administrador o no
        $coordinacionId = $usuario->rol === 'administrador' ? null : $usuario->coordinacion_id;

        // Filtra los grupos según la coordinación seleccionada
        $grupos = Grupo::with('coordinacion')
            ->when($coordinacionId, fn($query) => $query->where('coordinacion_id', $coordinacionId))
            ->orderBy('nombre')
            ->get();

        // Si el usuario es administrador, permite ver todas las coordinaciones
        $coordinaciones = $usuario->rol === 'administrador' ? Coordinacion::orderBy('nombre')->get() : [];

        // Filtra los cursos basados en el grupo y la coordinación
        $cursos = Curso::with('grupo', 'grupo.coordinacion')  // Asegúrate de que los cursos estén relacionados a grupos y coordinaciones
            ->when($coordinacionId, fn($query) => $query->whereHas('grupo', fn($q) => $q->where('coordinacion_id', $coordinacionId)))
            ->when($request->grupo_id, fn($query) => $query->where('grupo_id', $request->grupo_id)) // Filtra por grupo si es seleccionado
            ->get();

        return view('admin.grupos.index', compact('grupos', 'coordinaciones', 'usuario', 'coordinacionId', 'cursos'));
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

        return redirect()->route('grupos.index')->with('success', 'Grupo registrado correctamente.');
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
}
