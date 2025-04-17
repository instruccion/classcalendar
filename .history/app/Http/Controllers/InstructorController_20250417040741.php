<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Coordinacion;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstructorController extends Controller
{
    public function index()
    {
        $instructores = Instructor::all(); // Obtener todos los instructores
        return view('admin.instructores.index', compact('instructores')); // Retornar la vista con la lista
    }

    // Método para mostrar el formulario de creación de un nuevo instructor
    public function create()
    {
        $coordinaciones = Coordinacion::all(); // Obtener todas las coordinaciones
        $cursos = Curso::all(); // Obtener todos los cursos
        return view('admin.instructores.partials.modal-form', compact('coordinaciones', 'cursos'));
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

    // Método para mostrar el formulario de edición de un instructor
    public function edit(Instructor $instructor)
    {
        $coordinaciones = Coordinacion::all(); // Obtener todas las coordinaciones
        $cursos = Curso::all(); // Obtener todos los cursos
        return view('admin.instructores.partials.modal-form', compact('instructor', 'coordinaciones', 'cursos'));
    }



    // Método para actualizar un instructor existente
    public function update(Request $request, $id)
    {
        // Validación de los datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'especialidad' => 'nullable|string|max:255',
            'coordinacion_ids' => 'required|array',
            'curso_ids' => 'required|array',
        ]);

        // Usar transacción para evitar errores
        DB::transaction(function () use ($id, $validated) {
            $instructor = Instructor::findOrFail($id);

            $instructor->update([
                'nombre' => $validated['nombre'],
                'especialidad' => $validated['especialidad'] ?? null,
            ]);

            $instructor->coordinaciones()->sync($validated['coordinacion_ids']);
            $instructor->cursos()->sync($validated['curso_ids']);
        });

        return redirect()->route('admin.instructores.index')->with('toast', [
            'type' => 'success',
            'message' => 'Instructor actualizado exitosamente.',
        ]);
    }

}
