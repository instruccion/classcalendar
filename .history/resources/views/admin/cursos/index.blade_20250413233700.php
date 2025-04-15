<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 text-center mb-6">📅 Calendario de Cursos</h1>
        <button onclick="document.getElementById('modalNuevoCurso').showModal()"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            ➕ Registrar Nuevo Curso
        </button>

        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 justify-center mb-6 max-w-4xl mx-auto">
            @if ($usuario->rol === 'administrador')
                <div>
                    <label for="coordinacion" class="block text-sm font-medium text-gray-700">Coordinación:</label>
                    <select id="coordinacion" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                        <option value="">Todas</option>
                        @foreach ($coordinaciones as $coor)
                            <option value="{{ $coor->id }}">{{ $coor->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="grupo" class="block text-sm font-medium text-gray-700">Grupo:</label>
                <select name="grupo" id="grupo" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                    <option value="">Todos los grupos</option>
                    @foreach ($grupos as $grupo)
                        <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">
                    Filtrar
                </button>
            </div>
        </form>

    </div>

    <div id="calendar-container" class="bg-white rounded-lg shadow p-4 mt-6"
         data-coordinacion-id="{{ $coordinacionId }}">
        <div id="calendar" class="w-full"></div>
    </div>

    <dialog id="modalNuevoCurso" class="rounded-lg w-full max-w-4xl p-0 overflow-hidden shadow-xl backdrop:bg-black/30">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h2 class="text-xl font-bold">Registrar Nuevo Curso</h2>
                <button onclick="document.getElementById('modalNuevoCurso').close()"
                        class="text-gray-600 hover:text-black text-xl">&times;</button>
            </div>

            <form action="{{ route('cursos.store') }}" method="POST" class="grid grid-cols-12 gap-4">
                @csrf

                {{-- Asignación a grupos --}}
                <div class="col-span-12">
                    <label class="block font-semibold mb-1">Asignar a Grupo(s)</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach ($grupos as $g)
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="grupo_ids[]" value="{{ $g->id }}">
                                {{ $g->nombre }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Nombre del curso --}}
                <div class="col-span-12 md:col-span-6">
                    <label class="block font-semibold mb-1">Nombre del Curso</label>
                    <input type="text" name="nombre" required maxlength="100"
                        value="{{ old('nombre') }}"
                        class="w-full border px-4 py-2 rounded">
                </div>

                {{-- Tipo --}}
                <div class="col-span-12 md:col-span-4">
                    <label class="block font-semibold mb-1">Tipo</label>
                    <select name="tipo" class="w-full border px-4 py-2 rounded" required>
                        <option value="Inicial" {{ old('tipo') === 'Inicial' ? 'selected' : '' }}>Inicial</option>
                        <option value="Periódico" {{ old('tipo') === 'Periódico' ? 'selected' : '' }}>Periódico</option>
                        <option value="General" {{ old('tipo') === 'General' ? 'selected' : '' }}>General</option>
                    </select>
                </div>

                {{-- Duración --}}
                <div class="col-span-12 md:col-span-2">
                    <label class="block font-semibold mb-1">Duración (horas)</label>
                    <input type="number" name="duracion_horas" required min="1"
                        value="{{ old('duracion_horas') }}"
                        class="w-full border px-4 py-2 rounded">
                </div>

                {{-- Descripción --}}
                <div class="col-span-12">
                    <label class="block font-semibold mb-1">Descripción</label>
                    <textarea name="descripcion" rows="3" class="w-full border px-4 py-2 rounded">{{ old('descripcion') }}</textarea>
                </div>

                {{-- Botón --}}
                <div class="col-span-12 text-center mt-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Registrar Curso
                    </button>
                </div>
            </form>
        </div>
    </dialog>

</x-app-layout>
