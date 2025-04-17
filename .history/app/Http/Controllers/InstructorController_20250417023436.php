<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Curso;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstructorController extends Controller
{
    public function index()
    {
        $instructores = Instructor::with(['coordinaciones', 'cursos'])->orderBy('nombre')->get();
        $coordinaciones = Coordinacion::orderBy('nombre')->get();
        $cursos = Curso::orderBy('nombre')->get();

        return view('admin.instructores.index', compact('instructores', 'coordinaciones', 'cursos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:191',
            'especialidad' => 'nullable|string|max:191',
            'correo' => 'nullable|email|max:191',
            'telefono' => 'nullable|regex:/^\+\d{1,4}\s?\d{3,14}$/',
            'coordinaciones' => 'array',
            'coordinaciones.*' => 'exists:coordinaciones,id',
            'cursos' => 'array',
            'cursos.*' => 'exists:cursos,id',
        ]);

        DB::transaction(function () use ($validated) {
            $instructor = Instructor::create([
                'nombre' => $validated['nombre'],
                'especialidad' => $validated['especialidad'] ?? null,
            ]);

            $instructor->coordinaciones()->sync($validated['coordinacion_ids']);
            $instructor->cursos()->sync($validated['curso_ids']);
        });

        return redirect()->route('admin.instructores.index')
            ->with(toast('success', 'Instructor registrado correctamente.'));
    }

    public function update(Request $request, Instructor $instructor)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'especialidad' => 'nullable|string|max:100',
            'coordinacion_ids' => 'required|array|min:1',
            'coordinacion_ids.*' => 'exists:coordinaciones,id',
            'curso_ids' => 'required|array|min:1',
            'curso_ids.*' => 'exists:cursos,id',
        ]);

        DB::transaction(function () use ($instructor, $validated) {
            $instructor->update([
                'nombre' => $validated['nombre'],
                'especialidad' => $validated['especialidad'] ?? null,
            ]);

            $instructor->coordinaciones()->sync($validated['coordinacion_ids']);
            $instructor->cursos()->sync($validated['curso_ids']);
        });

        return redirect()->route('admin.instructores.index')
            ->with(toast('success', 'Instructor actualizado correctamente.'));
    }

    public function destroy(Instructor $instructor)
    {
        $instructor->delete();

        return redirect()->route('admin.instructores.index')
            ->with(toast('success', 'Instructor eliminado correctamente.'));
    }
}
