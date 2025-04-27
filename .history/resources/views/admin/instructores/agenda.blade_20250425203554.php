<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            📅 Agenda de Instructores
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto space-y-6">
        {{-- Filtro selector de instructores --}}
        <form method="GET" action="{{ route('admin.instructores.agenda') }}" class="mb-4 max-w-md">
            <label for="instructor_id" class="block font-semibold mb-1">Selecciona un instructor:</label>
            <select name="instructor_id" id="instructor_id" class="w-full border px-4 py-2 rounded" onchange="this.form.submit()">
                <option value="">-- Elegir instructor --</option>
                @foreach($instructores as $inst)
                    <option value="{{ $inst->id }}" {{ request('instructor_id') == $inst->id ? 'selected' : '' }}>
                        {{ $inst->nombre }}
                    </option>
                @endforeach
            </select>
        </form>

        {{-- Tabla de cursos asignados --}}
        @if($programaciones->count())
            <div class="bg-white shadow p-4 rounded overflow-x-auto">
                <h3 class="font-bold text-lg mb-3">Cursos asignados</h3>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-4 py-2">Curso</th>
                            <th class="px-4 py-2">Grupo</th>
                            <th class="px-4 py-2">Inicio</th>
                            <th class="px-4 py-2">Fin</th>
                            <th class="px-4 py-2">Horario</th>
                            <th class="px-4 py-2">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($programaciones as $p)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $p->curso->nombre ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $p->grupo->nombre ?? '—' }}</td>
                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($p->fecha_inicio)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($p->fecha_fin)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2">{{ $p->hora_inicio }} - {{ $p->hora_fin }}</td>
                                <td class="px-4 py-2">
                                    @if($p->estado_confirmacion === 'confirmado')
                                        <span class="text-green-600 font-semibold">Confirmado</span>
                                    @elseif($p->estado_confirmacion === 'rechazado')
                                        <span class="text-red-600 font-semibold">Rechazado</span>
                                    @else
                                        <span class="text-yellow-600 font-semibold">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-white shadow p-4 rounded text-gray-600">
                No hay programaciones asignadas para este instructor.
            </div>
        @endif

        {{-- Calendario --}}
        <div class="bg-white shadow p-6 rounded">
            <div id='calendar' class="w-full"></div>
        </div>
    </div>

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                events: async function (fetchInfo, successCallback, failureCallback) {
                    try {
                        const instructorId = "{{ request('instructor_id') }}";
                        const url = instructorId ? `/api/mi-agenda?instructor_id=${instructorId}` : `/api/mi-agenda`;

                        const response = await fetch(url);
                        const data = await response.json();

                        const eventos = data.map(item => ({
                            title: item.titulo,
                            start: item.inicio,
                            end: item.fin,
                            extendedProps: item.extendedProps,
                            color: item.color || '#2563EB',
                        }));

                        successCallback(eventos);
                    } catch (e) {
                        failureCallback(e);
                    }
                },

                eventClick: function(info) {
                    const props = info.event.extendedProps;
                    const detalle = `Curso: ${info.event.title}\nGrupo: ${props.grupo}\nHorario: ${props.hora_inicio} - ${props.hora_fin}\nLugar: ${props.aula ?? 'N/A'}`;
                    alert(detalle);
                },
            });

            calendar.render();
        });
    </script>
</x-app-layout>
