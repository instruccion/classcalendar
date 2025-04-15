<!-- Esta vista parcial no debe tener layout completo -->
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            @if ($usuario->rol === 'administrador')
                <div>
                    <label for="coordinacion" class="block text-sm font-medium text-gray-700">Coordinación:</label>
                    <select name="coordinacion_id" id="coordinacion" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                        <option value="">Todas</option>
                        @foreach ($coordinaciones as $coor)
                            <option value="{{ $coor->id }}" {{ request('coordinacion_id') == $coor->id ? 'selected' : '' }}>
                                {{ $coor->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="grupo" class="block text-sm font-medium text-gray-700">Grupo:</label>
                <select name="grupo_id" id="grupo" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
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

        <button onclick="document.getElementById('modalNuevoCurso').showModal()"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            ➕ Registrar Nuevo Curso
        </button>
    </div>

    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-left">Tipo</th>
                    <th class="px-4 py-2 text-left">Duración</th>
                    <th class="px-4 py-2 text-left">Grupos</th>
                    <th class="px-4 py-2 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cursos as $curso)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium text-gray-800">{{ $curso->nombre }}</td>
                        <td class="px-4 py-2">{{ ucfirst($curso->tipo) }}</td>
                        <td class="px-4 py-2">{{ $curso->duracion_horas }} h</td>
                        <td class="px-4 py-2">
                            {{ $curso->grupos->pluck('nombre')->join(', ') }}
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ route('cursos.edit', $curso->id) }}" class="text-blue-600 hover:underline">Editar</a>
                            <form action="{{ route('cursos.destroy', $curso->id) }}" method="POST" class="inline-block ml-2"
                                  onsubmit="return confirm('¿Deseas eliminar este curso?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">No hay cursos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<dialog id="modalNuevoCurso" class="rounded-lg w-full max-w-4xl p-0 overflow-hidden shadow-xl backdrop:bg-black/30">
    <div class="bg-white p-6">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h2 class="text-xl font-bold">Registrar Nuevo Curso</h2>
            <button onclick="document.getElementById('modalNuevoCurso').close()" class="text-gray-600 hover:text-black text-xl">&times;</button>
        </div>

        <form action="{{ route('cursos.store') }}" method="POST" class="grid grid-cols-12 gap-4">
            @csrf

            <div class="col-span-12">
                <label class="block font-semibold mb-1">Asignar a Grupo(s)</label>
                @if ($grupos->isEmpty())
                    <p class="text-sm text-gray-500">No hay grupos disponibles.</p>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach ($grupos as $g)
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="grupo_ids[]" value="{{ $g->id }}">
                                {{ $g->nombre }}
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="block font-semibold mb-1">Nombre del Curso</label>
                <input type="text" name="nombre" required maxlength="100" value="{{ old('nombre') }}" class="w-full border px-4 py-2 rounded">
            </div>

            <div class="col-span-12 md:col-span-4">
                <label class="block font-semibold mb-1">Tipo</label>
                <select name="tipo" class="w-full border px-4 py-2 rounded" required>
                    <option value="inicial" {{ old('tipo') === 'inicial' ? 'selected' : '' }}>Inicial</option>
                    <option value="recurrente" {{ old('tipo') === 'recurrente' ? 'selected' : '' }}>Recurrente</option>
                    <option value="puntual" {{ old('tipo') === 'puntual' ? 'selected' : '' }}>Puntual</option>
                </select>
            </div>

            <div class="col-span-12 md:col-span-2">
                <label class="block font-semibold mb-1">Duración (horas)</label>
                <input type="number" name="duracion_horas" required min="1" value="{{ old('duracion_horas') }}" class="w-full border px-4 py-2 rounded">
            </div>

            <div class="col-span-12">
                <label class="block font-semibold mb-1">Descripción (opcional)</label>
                <textarea name="descripcion" rows="3" class="w-full border px-4 py-2 rounded">{{ old('descripcion') }}</textarea>
            </div>

            <div class="col-span-12 text-center mt-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Registrar Curso
                </button>
            </div>
        </form>
    </div>
</dialog>
