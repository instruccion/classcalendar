<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Gestor de Cursos
        </h2>
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
                    <select id="coordinacion" name="coordinacion_id" class="mt-1 block w-full border rounded px-3 py-2" onchange="updateGrupos()">
                        <option value="">Todas</option>
                        @foreach ($coordinaciones ?? [] as $coor)
                            <option value="{{ $coor->id }}" {{ request('coordinacion_id') == $coor->id ? 'selected' : '' }}>
                                {{ $coor->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Filtro por Grupo -->
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
                                <a href="{{ route('admin.cursos.edit', $curso) }}" class="text-blue-600 hover:underline">Editar</a>
                                <form action="{{ route('admin.grupos.destroy', $grupo) }}" method="POST" onsubmit="return confirm('Confirma la eliminación del curso?')">
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

    <script>
        // Función AJAX para actualizar los grupos cuando se cambia la coordinación
        function updateGrupos() {
            var coordinacion_id = document.getElementById('coordinacion').value;

            // Realizar una petición AJAX para obtener los grupos de la coordinación seleccionada
            fetch(`/grupos?coordinacion_id=${coordinacion_id}`)
                .then(response => response.json())
                .then(data => {
                    var grupoSelect = document.getElementById('grupo');
                    grupoSelect.innerHTML = '<option value="">Todos los grupos</option>'; // Limpiar las opciones actuales

                    // Agregar los nuevos grupos a la lista
                    data.grupos.forEach(grupo => {
                        var option = document.createElement('option');
                        option.value = grupo.id;
                        option.textContent = grupo.nombre;
                        grupoSelect.appendChild(option);
                    });
                });
        }

        
    </script>
</x-app-layout>
