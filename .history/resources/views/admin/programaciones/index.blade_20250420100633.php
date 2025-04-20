<x-app-layout>
    <div class="py-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Programaciones</h1>
            <a href="{{ route('admin.programaciones.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                ➕ Nueva Programación
            </a>
        </div>

        <div class="bg-white p-4 rounded shadow-md mb-4">
            <form method="GET" action="{{ route('admin.programaciones.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @if(auth()->user()->esAdministrador() && is_null(auth()->user()->coordinacion_id))
                    <div>
                        <label for="coordinacion_id" class="block text-sm font-semibold">Coordinación</label>
                        <select name="coordinacion_id" id="coordinacion_id" class="w-full border px-4 py-2 rounded">
                            <option value="">Todas</option>
                            @foreach($coordinaciones as $coordinacion)
                                <option value="{{ $coordinacion->id }}" {{ request('coordinacion_id') == $coordinacion->id ? 'selected' : '' }}>
                                    {{ $coordinacion->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label for="grupo_id" class="block text-sm font-semibold">Grupo</label>
                    <select name="grupo_id" id="grupo_id" class="w-full border px-4 py-2 rounded">
                        <option value="">Todos</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="buscar" class="block text-sm font-semibold">Buscar</label>
                    <input type="text" name="buscar" id="buscar" placeholder="Curso, grupo, instructor..." value="{{ request('buscar') }}" class="w-full border px-4 py-2 rounded">
                </div>

                <div class="md:col-span-4 text-right">
                    <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">🔍 Buscar</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full table-auto text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Grupo</th>
                        <th class="px-4 py-2 text-left">Bloque</th>
                        <th class="px-4 py-2 text-left">Curso</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Duración</th>
                        <th class="px-4 py-2 text-left">Aula</th>
                        <th class="px-4 py-2 text-left">Instructor</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($programaciones as $programacion)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $programacion->grupo->nombre }}</td>
                            <td class="px-4 py-2">{{ $programacion->bloque_codigo ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $programacion->curso->nombre }}</td>
                            <td class="px-4 py-2">{{ ucfirst($programacion->curso->tipo ?? '-') }}</td>
                            <td class="px-4 py-2">{{ $programacion->curso->duracion_horas }}h</td>
                            <td class="px-4 py-2">{{ $programacion->aula->nombre ?? '-' }}</td>
                            <td class="px-4 py-2">
                                @if($programacion->instructor)
                                    <a href="mailto:{{ $programacion->instructor->correo }}" class="text-blue-600 hover:underline">
                                        {{ $programacion->instructor->nombre }}
                                    </a>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 flex gap-2">
                                <a href="{{ route('admin.programaciones.edit', $programacion) }}" class="text-blue-600 hover:underline text-sm">Editar</a>
                                <form action="{{ route('admin.programaciones.destroy', $programacion) }}" method="POST" onsubmit="return confirm('¿Eliminar esta programación?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-sm">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">No hay programaciones disponibles.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $programaciones->appends(request()->query())->links() }}
        </div>
    </div>
</x-app-layout>
