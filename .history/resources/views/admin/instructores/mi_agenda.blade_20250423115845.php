<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Mi Agenda
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto">
        <div class="bg-white shadow p-6 rounded">
            <div id='calendar' class="w-full"></div>
        </div>
    </div>

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
                        const response = await fetch("{{ route('api.mi-agenda') }}");
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
