<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">Gestión de Cursos</h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <div class="mb-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Cursos Registrados</h1>
            <button onclick="document.getElementById('modalNuevoCurso').showModal()"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Registrar Nuevo Curso
            </button>
        </div>

        <!-- Filtros -->
        <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            @if ($usuario->rol === 'administrador')
                <div>
                    <label for="coordinacion" class="block text-sm font-medium text-gray-700">Coordinación</label>
                    <select id="coordinacion" name="coordinacion_id" class="mt-1 block w-full border rounded px-3 py-2">
                        <option value="">Todas</option>
                        @foreach ($coordinaciones ?? [] as $coor)
                            <option value="{{ $coor->id }}" {{ request('coordinacion_id') == $coor->id ? 'selected' : '' }}>
                                {{ $coor->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="grupo" class="block text-sm font-medium text-gray-700">Grupo</label>
                <select name="grupo_id" id="grupo" class="mt-1 block w-full border rounded px-3 py-2">
                    <option value="">Todos los grupos</option>
                    @foreach ($grupos as $grupo)
                        <option value="{{ $grupo->id }}" {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>
                            {{ $grupo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Filtrar
                </button>
            </div>
        </form>

        <!-- Tabla de cursos -->
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2">Nombre</th>
                        <th class="px-4 py-2">Tipo</th>
                        <th class="px-4 py-2">Duración</th>
                        <th class="px-4 py-2">Grupos</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cursos as $curso)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $curso->nombre }}</td>
                            <td class="px-4 py-2">{{ $curso->tipo }}</td>
                            <td class="px-4 py-2">{{ $curso->duracion_horas }} h</td>
                            <td class="px-4 py-2">
                                {{ $curso->grupos->pluck('nombre')->join(', ') }}
                            </td>
                            <td class="px-4 py-2 flex gap-2">
                            <button type="button" onclick="abrirModalEditarCurso({{ $curso->id }})"
                                class="text-blue-600 hover:underline">Editar</button>

                                <form action="{{ route('admin.cursos.destroy', $curso) }}" method="POST" onsubmit="return confirm('¿Eliminar este curso?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">No hay cursos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal de nuevo curso -->
        @include('admin.cursos.partials.modal-nuevo')
    </div>

    {{-- Modal para editar curso --}}
    @include('admin.cursos.partials.modal-editar')

    <script>
        const BASE_URL_EDITAR_CURSO = "{{ url('admin/cursos') }}";

        function abrirModalEditarCurso(cursoId) {
            fetch(`${BASE_URL_EDITAR_CURSO}/${cursoId}/edit`)
                .then(response => {
                    if (!response.ok) throw new Error('Error de red');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        Livewire?.emit?.('toast', { type: 'error', message: data.error });
                        return;
                    }

                    // Rellenar campos
                    document.getElementById('curso_edit_id').value = data.id;
                    document.getElementById('curso_edit_nombre').value = data.nombre;
                    document.getElementById('curso_edit_tipo').value = data.tipo;
                    document.getElementById('curso_edit_duracion').value = data.duracion_horas;
                    document.getElementById('curso_edit_descripcion').value = data.descripcion ?? '';

                    // Desmarcar todos los checkboxes
                    document.querySelectorAll('#curso_edit_grupos input[type=checkbox]').forEach(cb => cb.checked = false);

                    // Marcar los que corresponden
                    if (Array.isArray(data.grupo_ids)) {
                        data.grupo_ids.forEach(id => {
                            const checkbox = document.querySelector(`#curso_edit_grupos input[value="${id}"]`);
                            if (checkbox) checkbox.checked = true;
                        });
                    }

                    // Establecer acción del formulario
                    const formEditar = document.getElementById('formEditarCurso');
                    formEditar.action = `${BASE_URL_EDITAR_CURSO}/${cursoId}`;

                    // Mostrar modal
                    document.getElementById('modalEditarCurso').showModal();
                })
                .catch(() => {
                    Livewire?.emit?.('toast', { type: 'error', message: 'Error al cargar datos del curso.' });
                });
        }

        
    </script>




</x-app-layout>
