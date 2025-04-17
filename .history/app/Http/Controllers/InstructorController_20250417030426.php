<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Fa cades\DB;

class InstructorController extends Controller
{
    public function index()
    {
        $instructores = Instructor::all(); // Obtener todos los instructores
        return view('admin.instructores.index', compact('instructores')); // Retornar la vista con la lista
    }

    // Método para registrar un nuevo instructor
    public function store(Request $request)
    {
        // Validación de los datos recibidos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'especialidad' => 'nullable|string|max:255',
            'coordinacion_ids' => 'required|array',
            'curso_ids' => 'required|array',
        ]);

        // Crear un nuevo instructor
        $instructor = Instructor::create([
            'nombre' => $validated['nombre'],
            'especialidad' => $validated['especialidad'] ?? null,
        ]);

        // Sincronizar las coordinaciones
        $instructor->coordinaciones()->sync($validated['coordinacion_ids']);

        // Sincronizar los cursos
        $instructor->cursos()->sync($validated['curso_ids']);

        // Redirigir al listado de instructores con un mensaje de éxito
        return redirect()->route('admin.instructores.index')->with('toast', [
            'type' => 'success',
            'message' => 'Instructor registrado exitosamente.',
        ]);
    }

    // Método para actualizar un instructor existente
    public function update(Request $request, Instructor $instructor)
    {
        // Validación de los datos recibidos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'especialidad' => 'nullable|string|max:255',
            'coordinacion_ids' => 'required|array',
            'curso_ids' => 'required|array',
        ]);

        // Usar transacción para asegurar que se actualicen correctamente los datos
        DB::transaction(function () use ($instructor, $validated) {
            // Actualizar los datos del instructor
            $instructor->update([
                'nombre' => $validated['nombre'],
                'especialidad' => $validated['especialidad'] ?? null,
            ]);

            // Sincronizar las coordinaciones
            $instructor->coordinaciones()->sync($validated['coordinacion_ids']);

            // Sincronizar los cursos
            $instructor->cursos()->sync($validated['curso_ids']);
        });

        // Redirigir al listado de instructores con un mensaje de éxito
        return redirect()->route('admin.instructores.index')->with('toast', [
            'type' => 'success',
            'message' => 'Instructor actualizado exitosamente.',
        ]);
    }
}
