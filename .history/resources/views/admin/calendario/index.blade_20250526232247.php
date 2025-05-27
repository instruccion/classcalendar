<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 text-center mb-6">📅 Calendario de Cursos</h1>

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
    @push('scripts')
        @vite(['resources/js/calendario.js'])
    @endpush

</x-app-layout>
