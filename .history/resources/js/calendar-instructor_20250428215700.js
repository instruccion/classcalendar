import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('instructor-agenda-calendar');
    const instructorId = document.getElementById('instructor_id')?.value || window.instructorActualId;

    console.log('calendar-instructor.js INICIANDO...');

    if (!calendarEl || !instructorId) {
        console.log('No hay calendario o instructor.');
        return;
    }

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: esLocale,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        slotMinTime: '08:00:00',
        slotMaxTime: '18:00:00',
        weekends: true,

        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('/api/instructor-agenda?instructor_id=' + instructorId)
                .then(response => response.json())
                .then(data => {
                    const filteredEvents = [];

                    data.forEach(event => {
                        const startDate = new Date(event.start);
                        const endDate = event.end ? new Date(event.end) : startDate;

                        const startDay = startDate.getDay();
                        const endDay = endDate.getDay();

                        // Permitir mostrar:
                        // - Si la fecha de inicio es sábado o domingo
                        // - Si la fecha de fin es sábado o domingo
                        // - O si es un día entre semana (lunes a viernes)
                        const isStartWeekend = (startDay === 6 || startDay === 0);
                        const isEndWeekend = (endDay === 6 || endDay === 0);

                        if (!((startDay === 6 || startDay === 0) && !isStartWeekend && !isEndWeekend)) {
                            filteredEvents.push(event);
                        }
                    });

                    successCallback(filteredEvents);
                })
                .catch(error => {
                    console.error('Error al cargar eventos:', error);
                    failureCallback(error);
                });
        },

        eventClick: function (info) {
            const props = info.event.extendedProps;

            const modal = document.getElementById('modalDetalle');
            const contenido = `
                <p><strong>Curso:</strong> ${info.event.title}</p>
                <p><strong>Grupo:</strong> ${props.grupo}</p>
                <p><strong>Aula:</strong> ${props.aula}</p>
                <p><strong>Fecha Inicio:</strong> ${props.fecha_inicio_fmt}</p>
                <p><strong>Fecha Fin:</strong> ${props.fecha_fin_fmt}</p>
                <p><strong>Horario:</strong> ${props.hora_inicio} - ${props.hora_fin}</p>
                <p><strong>Estado:</strong> ${props.estadoDisplay}</p>
            `;
            document.getElementById('modalContent').innerHTML = contenido;
            modal.showModal();
        },

        eventDisplay: 'block',

        dayCellDidMount: function (info) {
            const day = info.date.getDay();
            const dateStr = info.date.toISOString().split('T')[0];

            const hasEvent = calendar.getEvents().some(event => {
                const startDate = event.startStr.split('T')[0];
                const endDate = event.endStr ? event.endStr.split('T')[0] : startDate;
                return dateStr >= startDate && dateStr <= endDate;
            });

            // Sábado o domingo sin eventos => fondo gris
            if ((day === 0 || day === 6) && !hasEvent) {
                info.el.style.backgroundColor = '#f8f9fa'; // Gris clarito
            }
        }
    });

    calendar.render();
});
