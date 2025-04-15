<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Coordinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $coordinacionId = $usuario->rol === 'administrador' ? null : $usuario->coordinacion_id;

        // Obtener coordinaciones solo si el usuario es administrador
        $coordinaciones = $usuario->rol === 'administrador' ? Coordinacion::all() : [];

        // Filtro de grupos según la coordinación
        $grupos = Grupo::when($coordinacionId, function ($q) use ($coordinacionId) {
            $q->where('coordinacion_id', $coordinacionId);
        })->orderBy('nombre')->get();

        // Filtrar los cursos según el grupo seleccionado
        $grupoSeleccionadoId = $request->input('grupo_id');
        $cursos = Curso::with('grupos')
            ->when($grupoSeleccionadoId, function ($query) use ($grupoSeleccionadoId) {
                $query->whereHas('grupos', function ($q) use ($grupoSeleccionadoId) {
                    $q->where('grupo_id', $grupoSeleccionadoId);
                });
            })
            ->when($coordinacionId, function ($query) use ($coordinacionId) {
                $query->whereHas('grupos', function ($q) use ($coordinacionId) {
                    $q->where('coordinacion_id', $coordinacionId);
                });
            })
            ->orderBy('nombre')
            ->get();

        return view('admin.cursos.index', [
            'usuario' => $usuario,
            'coordinaciones' => $coordinaciones,
            'grupos' => $grupos,
            'cursos' => $cursos,
            'coordinacionId' => $coordinacionId
        ]);
    }

    // Método para guardar los cambios en el curso
    public function store(Request $request)
    {
        // Validación de datos
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|string',
            'descripcion' => 'nullable|string',
            'duracion_horas' => 'required|integer|min:1',
            'grupo_ids' => 'required|array',
            'grupo_ids.*' => 'exists:grupos,id',
        ]);

        // Crear el curso
        $curso = Curso::create([
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'duracion_horas' => $validated['duracion_horas'],
        ]);

        // Asociar los grupos seleccionados al curso
        $curso->grupos()->sync($validated['grupo_ids']);

        // Auditoría
        registrar_auditoria("Curso creado", "Se registró el curso: {$curso->nombre}");

        return redirect()->route('admin.cursos.index')->with('success', 'Curso creado exitosamente.');
    }


    public function update(Request $request, Curso $curso)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|string',
            'descripcion' => 'nullable|string',
            'duracion_horas' => 'required|integer|min:1',
            'grupo_ids' => 'required|array',
            'grupo_ids.*' => 'exists:grupos,id',
        ]);

        // Actualizar el curso
        $curso->update([
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'descripcion' => $validated['descripcion'],
            'duracion_horas' => $validated['duracion_horas'],
        ]);

        // Actualizar las asociaciones con grupos
        $curso->grupos()->sync($validated['grupo_ids']);

        // Auditoría
        registrar_auditoria("Curso actualizado", "Se modificó el curso: {$curso->nombre}");

        return redirect()->route('cursos.index')->with('success', 'Curso actualizado exitosamente.');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const coordinacionSelect = document.getElementById('coordinacion');
        const grupoSelect = document.getElementById('grupo');

        if (coordinacionSelect) { // Asegurar que el elemento existe
            coordinacionSelect.addEventListener('change', async function() { // Usar async/await es más limpio
                const coordinacionId = this.value;

                grupoSelect.disabled = true;
                grupoSelect.innerHTML = '<option value="">Cargando grupos...</option>';

                let apiUrl = '';
                if (coordinacionId) {
                    // Usa el nombre de ruta correcto con el prefijo 'admin.'
                    apiUrl = `{{ route('admin.grupos.por.coordinacion', ['coordinacion' => ':id']) }}`.replace(':id', coordinacionId);
                } else {
                    // Usa el nombre de ruta correcto con el prefijo 'admin.'
                    apiUrl = `{{ route('admin.grupos.visibles') }}`;
                }

                try {
                    const response = await fetch(apiUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            // Si necesitas CSRF para rutas GET (inusual pero posible si cambias a POST)
                            // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) {
                        let errorMsg = `Error ${response.status}`;
                         try { // Intenta obtener un mensaje más detallado del JSON de error
                             const errorData = await response.json();
                             errorMsg = errorData.message || errorMsg;
                         } catch(e) {}
                        throw new Error(errorMsg);
                    }

                    const data = await response.json(); // Espera {'grupos': [...]}

                    grupoSelect.innerHTML = ''; // Limpiar
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = 'Todos los grupos';
                    grupoSelect.appendChild(defaultOption);

                    if (data.grupos && data.grupos.length > 0) {
                        data.grupos.forEach(grupo => {
                            const option = document.createElement('option');
                            option.value = grupo.id;
                            option.textContent = grupo.nombre;
                            // Marcar si coincide con el valor seleccionado actualmente (si la página se recargó)
                            // Esto requiere que pases $selectedGrupoId a esta vista desde CursoController
                            const selectedGrupoId = "{{ $selectedGrupoId ?? '' }}"; // Obtener de Blade
                            if(selectedGrupoId && grupo.id == selectedGrupoId) {
                                option.selected = true;
                            }
                            grupoSelect.appendChild(option);
                        });
                    } else if (coordinacionId) {
                         defaultOption.textContent = 'No hay grupos para esta coord.';
                         grupoSelect.innerHTML = '';
                         grupoSelect.appendChild(defaultOption);
                    } else {
                        defaultOption.textContent = 'No hay grupos disponibles';
                         grupoSelect.innerHTML = '';
                         grupoSelect.appendChild(defaultOption);
                    }

                } catch (error) {
                    console.error("Error al cargar grupos:", error);
                    grupoSelect.innerHTML = `<option value="">Error: ${error.message}</option>`;
                } finally {
                    grupoSelect.disabled = false;
                }
            });

             // Opcional: Disparar el evento 'change' al cargar la página si hay una coordinación preseleccionada
             // Esto cargará los grupos correctos si la página se recarga con un filtro activo.
             if (coordinacionSelect.value) {
                 coordinacionSelect.dispatchEvent(new Event('change'));
             }

        } // Fin if(coordinacionSelect)
    });
}
