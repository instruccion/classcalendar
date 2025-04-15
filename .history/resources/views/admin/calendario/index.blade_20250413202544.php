<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 text-center mb-6">📅 Calendario de Cursos</h1>

        <form method="GET" class="flex flex-wrap justify-center items-center gap-4">
            @if ($usuario->rol === 'administrador')
                <label for="coordinacion" class="text-gray-700 font-medium">Filtrar por coordinación:</label>
                <select id="coordinacion" class="border rounded px-3 py-1 text-sm">
                    <option value="">Todas</option>
                    @foreach ($coordinaciones as $coor)
                        <option value="{{ $coor->id }}">{{ $coor->nombre }}</option>
                    @endforeach
                </select>
            @endif

            <label for="grupo" class="text-gray-700 font-medium">Filtrar por grupo:</label>
            <select name="grupo" id="grupo" class="border rounded px-3 py-1 text-sm">
                <option value="">Todos los grupos</option>
                @foreach ($grupos as $grupo)
                    <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Filtrar</button>
        </form>
    </div>

    <div id="calendar-container" class="bg-white rounded-lg shadow p-4 mt-6"
         data-coordinacion-id="{{ $coordinacionId }}">
        <div id="calendar" class="w-full"></div>
    </div>
</x-app-layout>
