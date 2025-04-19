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
        $instructores = Instructor::with(['coordinaciones', 'cursos', 'documentos'])->get();

        foreach ($instructores as $instructor) {
            foreach ($instructor->documentos as $doc) {
                $doc->esta_vencido = $doc->pivot->fecha_vencimiento
                    ? now()->gt($doc->pivot->fecha_vencimiento)
                    : false;

                $doc->por_vencer = $doc->pivot->fecha_vencimiento
                    ? now()->diffInDays($doc->pivot->fecha_vencimiento, false) <= 30 && now()->lt($doc->pivot->fecha_vencimiento)
                    : false;
            }
        }

        return view('admin.instructores.index', compact('instructores'));
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
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'especialidad' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:20',
            'coordinacion_ids' => 'required|array',
            'curso_ids' => 'required|array',
        ]);

        $instructor = Instructor::create([
            'nombre' => $validated['nombre'],
            'especialidad' => $validated['especialidad'] ?? null,
            'correo' => $validated['correo'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
        ]);

        $instructor->coordinaciones()->sync($validated['coordinacion_ids']);
        $instructor->cursos()->sync($validated['curso_ids']);

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

    // Mostrar documentos asignados a un instructor
    public function documentos(Instructor $instructor)
    {
        $documentos = \App\Models\Documento::all(); // Cargar todos los tipos de documentos posibles
        return view('admin.instructores.documentos', compact('instructor', 'documentos'));
    }

    // Asignar un documento con fecha de vencimiento
    public function asignarDocumentoManual(Request $request, Instructor $instructor)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        // Si el documento ya existe, lo busca; si no, lo crea
        $documento = \App\Models\Documento::firstOrCreate(
            ['nombre' => $validated['nombre']],
            ['es_obligatorio' => false]
        );

        // Asignar al instructor
        $instructor->documentos()->syncWithoutDetaching([
            $documento->id => ['fecha_vencimiento' => $validated['fecha_vencimiento']]
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Documento manual asignado correctamente.',
        ]);
    }



}
