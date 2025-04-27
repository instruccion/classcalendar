<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">📅 Agenda de Instructores</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto space-y-6">
        {{-- Selector de Instructor --}}
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

        {{-- Tabla de Programaciones --}}
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
                                <td class="px-4 py-2">{{ $p->fecha_inicio->format('d/m/Y') }}</td>
                                <td class="px-4 py-2">{{ $p->fecha_fin->format('d/m/Y') }}</td>
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
        @endif

        {{-- Calendario --}}
        <div class="bg-white shadow p-6 rounded">
            <div id='calendar' class="w-full"></div>
        </div>
    </div>

    {{-- Modal --}}
    <dialog id="modalDetalle" class="rounded shadow p-4 w-full max-w-xl">
        <div class="text-lg font-bold mb-2">Detalles del Curso</div>
        <div id="modalContent" class="text-sm space-y-1"></div>
        <div class="mt-4 text-right">
            <button onclick="document.getElementById('modalDetalle').close()" class="bg-gray-200 px-4 py-1 rounded">Cerrar</button>
        </div>
    </dialog>

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>




    <script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const instructorSelect = document.getElementById('instructor_id');

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            let instructorId = instructorSelect.value;
            fetch(`/cursoslaser/public/index.php/api/mi-agenda${instructorId ? '?instructor_id=' + instructorId : ''}`)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            const props = info.event.extendedProps;
            const modal = document.getElementById('modalDetalle');
            const contenido = `
                <p><strong>Curso:</strong> ${info.event.title}</p>
                <p><strong>Grupo:</strong> ${props.grupo}</p>
                <p><strong>Aula:</strong> ${props.aula}</p>
                <p><strong>Fecha:</strong> ${info.event.start.toLocaleDateString()}</p>
                <p><strong>Horario:</strong> ${props.hora_inicio} - ${props.hora_fin}</p>
                <p><strong>Estado:</strong> ${props.estado}</p>
            `;
            document.getElementById('modalContent').innerHTML = contenido;
            modal.showModal();
        },
        eventDisplay: 'block'
    });

    calendar.render();

    // Refrescar eventos cuando cambia el instructor
    instructorSelect.addEventListener('change', function () {
        calendar.refetchEvents();
    });
});
</script>

</x-app-layout>
