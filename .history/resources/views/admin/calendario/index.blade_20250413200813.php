<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            📅 Calendario de Cursos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ALERTA DE CAMBIO DE CONTRASEÑA --}}
            @if ($requiereCambio)
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded">
                    🔐 Has iniciado sesión con una contraseña temporal.
                    <a href="{{ route('profile.edit') }}" class="underline ml-1">Cambiar contraseña</a>
                </div>
            @endif

            {{-- FILTROS --}}
            <form method="GET" class="bg-white p-4 rounded shadow mb-6 flex flex-wrap gap-4 items-center justify-start">
                @if (auth()->user()->rol === 'administrador')
                    <label class="text-sm font-semibold text-gray-700">Coordinación:</label>
                    <select id="coordinacion" class="border px-3 py-1 rounded text-sm">
                        <option value="">Todas</option>
                        @foreach ($coordinaciones as $coor)
                            <option value="{{ $coor->id }}">{{ $coor->nombre }}</option>
                        @endforeach
                    </select>
                @endif

                <label class="text-sm font-semibold text-gray-700">Grupo:</label>
                <select id="grupo" class="border px-3 py-1 rounded text-sm">
                    <option value="">Todos</option>
                    @foreach ($grupos as $grupo)
                        <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                    @endforeach
                </select>

                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                    Filtrar
                </button>
            </form>

            {{-- CALENDARIO --}}
            <div id="calendar-container" class="bg-white p-4 rounded shadow" data-coordinacion-id="{{ $coordinacionId }}">
                <div id="calendar" class="w-full"></div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const calendarEl = document.getElementById('calendar');
                const grupo = document.getElementById('grupo');
                const coordinacion = document.getElementById('coordinacion');
                const defaultCoordinacionId = document.getElementById('calendar-container').dataset.coordinacionId;

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'es',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                    },
                    events: obtenerUrlEventos(),
                    dateClick: function(info) {
                        window.location.href = `/programar_curso?fecha=${info.dateStr}`;
                    }
                });

                calendar.render();

                grupo?.addEventListener('change', actualizarEventos);
                coordinacion?.addEventListener('change', actualizarEventos);

                function actualizarEventos() {
                    calendar.removeAllEvents();
                    calendar.setOption('events', obtenerUrlEventos());
                    calendar.refetchEvents();
                }

                function obtenerUrlEventos() {
                    const grupoId = grupo?.value;
                    const coordinacionId = coordinacion?.value || defaultCoordinacionId;
                    let url = `/api/eventos?`;

                    if (grupoId) url += `grupo=${grupoId}&`;
                    if (coordinacionId) url += `coordinacion=${coordinacionId}`;

                    return url;
                }
            });
        </script>
    @endpush
</x-app-layout>
