<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Gestor de Cursos
        </h2>
    </x-slot>

    <div class="py-4 mx-auto max-w-7xl">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Cursos Registrados</h1>
            {{-- Considera usar un Gate aquí también si no todos pueden crear --}}
            {{-- @can('create', App\Models\Curso::class) --}}
            <button onclick="document.getElementById('modalNuevoCurso').showModal()"
                    class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                ➕ Registrar Nuevo Curso
            </button>
            {{-- @endcan --}}
        </div>

        <!-- Filtros -->
        {{-- Asegúrate que la ruta sea la correcta para el método index del controlador --}}
        <form method="GET" action="{{ route('admin.cursos.index') }}" class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            {{-- Mostrar filtro de coordinación solo si el usuario tiene permiso (Gate) --}}
            @can('view-coordination-filter')
                <div>
                    <label for="coordinacion" class="block text-sm font-medium text-gray-700">Coordinación</label>
                    <select id="coordinacion" name="coordinacion_id" class="block w-full px-3 py-2 mt-1 border rounded">
                        <option value="">Todas</option>
                        {{-- $coordinaciones solo tendrá datos si el Gate lo permite (ver controlador) --}}
                        @foreach ($coordinaciones as $coor)
                            <option value="{{ $coor->id }}" @selected($selectedCoordinacionId == $coor->id)>
                                {{ $coor->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                 {{-- Opcional: Mostrar la coordinación del usuario (si no es admin) como texto --}}
                 {{-- @if(Auth::user()->coordinacion) --}}
                 {{-- <div> --}}
                 {{--     <label class="block text-sm font-medium text-gray-700">Coordinación</label> --}}
                 {{--     <p class="mt-1">{{ Auth::user()->coordinacion->nombre }}</p> --}}
                 {{--     <input type="hidden" name="coordinacion_id" value="{{ Auth::user()->coordinacion_id }}"> --}}
                 {{-- </div> --}}
                 {{-- @endif --}}
                 <div></div> {{-- Placeholder para mantener el grid layout si el filtro no se muestra --}}
            @endcan

            <div>
                <label for="grupo" class="block text-sm font-medium text-gray-700">Grupo</label>
                <select name="grupo_id" id="grupo" class="block w-full px-3 py-2 mt-1 border rounded">
                    <option value="">Todos los grupos</option>
                    {{-- $gruposParaFiltro ya está filtrado por coordinación en el controlador si aplica --}}
                    @foreach ($gruposParaFiltro as $grupo)
                        <option value="{{ $grupo->id }}" @selected($selectedGrupoId == $grupo->id)>
                            {{ $grupo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                    <i class="mdi mdi-filter"></i> Filtrar {{-- Icono opcional --}}
                </button>
                {{-- Botón para limpiar filtros (opcional) --}}
                <a href="{{ route('admin.cursos.index') }}" class="px-4 py-2 ml-2 text-gray-600 bg-gray-200 rounded hover:bg-gray-300">
                    Limpiar
                </a>
            </div>
        </form>

        <!-- Tabla de cursos -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full text-sm">
                <thead class="text-left bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">Nombre</th>
                        <th class="px-4 py-2">Tipo</th>
                        <th class="px-4 py-2">Duración</th>
                        <th class="px-4 py-2">Grupos Asociados</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Usar $cursos->isEmpty() para un mensaje más explícito --}}
                    @if($cursos->isEmpty())
                         <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                                No se encontraron cursos con los filtros aplicados.
                            </td>
                        </tr>
                    @else
                        @foreach ($cursos as $curso)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $curso->nombre }}</td>
                                <td class="px-4 py-2">{{ $curso->tipo }}</td>
                                <td class="px-4 py-2">{{ $curso->duracion_horas }} h</td>
                                <td class="px-4 py-2">
                                    {{-- Esto funciona gracias al Eager Loading en el controlador --}}
                                    {{ $curso->grupos->pluck('nombre')->join(', ') ?: '-' }}
                                </td>
                                <td class="px-4 py-2">
                                     <div class="flex items-center gap-2">
                                         {{-- Considera usar Gates para edit/delete --}}
                                         {{-- @can('update', $curso) --}}
                                        <a href="{{ route('admin.cursos.edit', $curso) }}" class="text-blue-600 hover:underline" title="Editar">
                                            <i class="mdi mdi-pencil"></i> {{-- Icono opcional --}}
                                        </a>
                                         {{-- @endcan --}}
                                         {{-- @can('delete', $curso) --}}
                                        <form action="{{ route('admin.cursos.destroy', $curso) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este curso? Se desasociará de todos los grupos.')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline" title="Eliminar">
                                                 <i class="mdi mdi-delete"></i> {{-- Icono opcional --}}
                                            </button>
                                        </form>
                                         {{-- @endcan --}}
                                     </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

         <!-- Paginación -->
        @if ($cursos->hasPages())
            <div class="px-4 py-4 mt-4">
                {{ $cursos->appends(request()->query())->links() }} {{-- links() muestra los enlaces, appends() mantiene los filtros al cambiar de página --}}
            </div>
        @endif


        <!-- Modal de nuevo curso (Asegúrate que la ruta sea correcta) -->
        @include('admin.cursos.partials.modal-nuevo')

    </div>

    {{-- Script para filtro dinámico de grupos --}}
    {{-- RECOMENDACIÓN FUERTE: Mover este JS a un archivo como resources/js/cursos-filter.js --}}
    {{-- e importarlo en tu app.js que compila Vite --}}
    @push('scripts') {{-- Si tu app-layout tiene un @stack('scripts') al final --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const coordinacionSelect = document.getElementById('coordinacion');
            const grupoSelect = document.getElementById('grupo');

            // Solo añadir el listener si el select de coordinación existe (es decir, si el usuario es admin)
            if (coordinacionSelect) {
                coordinacionSelect.addEventListener('change', async (event) => {
                    const coordinacionId = event.target.value;

                    // Deshabilitar/mostrar carga en grupoSelect
                    grupoSelect.disabled = true;
                    grupoSelect.innerHTML = '<option value="">Cargando grupos...</option>';

                    try {
                        // Construye la URL dinámicamente
                        const url = coordinacionId
                            ? `{{ url('/api/grupos-por-coordinacion') }}/${coordinacionId}` // Usa url() para generar la base
                            : `{{ route('api.grupos.todos') }}`; // Usa route() para la ruta nombrada

                        const response = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest', // Importante para Laravel
                                // Si usas Sanctum y necesitas token (depende de tu setup de API auth)
                                // 'Authorization': `Bearer ${your_api_token}`
                                // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Si la API requiere CSRF
                            }
                        });

                        if (!response.ok) {
                             // Intentar leer mensaje de error si es JSON
                            let errorMsg = `Error HTTP: ${response.status}`;
                            try {
                                const errorData = await response.json();
                                errorMsg = errorData.message || errorMsg;
                            } catch(e) {}
                            throw new Error(errorMsg);
                        }

                        const grupos = await response.json(); // Espera [{id: 1, nombre: 'Grupo A'}, ...]

                        // Limpiar opciones actuales
                        grupoSelect.innerHTML = '';

                        // Añadir opción "Todos"
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = 'Todos los grupos';
                        grupoSelect.appendChild(defaultOption);

                        // Añadir nuevas opciones desde la respuesta API
                        if (grupos && grupos.length > 0) {
                            grupos.forEach(grupo => {
                                const option = document.createElement('option');
                                option.value = grupo.id;
                                option.textContent = grupo.nombre;
                                grupoSelect.appendChild(option);
                            });
                        } else if (coordinacionId) {
                            // Si seleccionó una coordinación pero no hay grupos, mostrar mensaje
                            grupoSelect.innerHTML = '<option value="">No hay grupos para esta coordinación</option>';
                        } else {
                             // Si seleccionó "Todas" y no hay grupos (raro, pero posible)
                             grupoSelect.innerHTML = '<option value="">No hay grupos disponibles</option>';
                        }

                    } catch (error) {
                        console.error('Error al cargar grupos:', error);
                        grupoSelect.innerHTML = `<option value="">Error: ${error.message}</option>`;
                    } finally {
                        // Habilitar el select de nuevo
                        grupoSelect.disabled = false;
                    }
                });
            } // fin if(coordinacionSelect)
        }); // fin DOMContentLoaded
    </script>
    @endpush

</x-app-layout>
