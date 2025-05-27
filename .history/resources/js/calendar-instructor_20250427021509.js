import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('instructor-agenda-calendar');
    const instructorId = document.getElementById('instructor_id')?.value;

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
            fetch(window.location.origin + '/cursoslaser/public/index.php/api/instructor-agenda?instructor_id=' + instructorId)
                .then(response => response.json())
                .then(data => {
                    const events = [];

                    data.forEach(event => {
                        // Detectar si el evento empieza en sábado (6) o domingo (0)
                        const startDate = new Date(event.start);
                        const dayOfWeek = startDate.getDay(); // 0=domingo, 6=sábado

                        if (dayOfWeek === 0 || dayOfWeek === 6) {
                            // Modificar el color de fondo y borde para eventos en fines de semana
                            event.backgroundColor = '#fde68a'; // amarillo claro
                            event.borderColor = '#2563eb';     // azul fuerte
                            event.textColor = '#1e3a8a';       // azul oscuro para que se lea
                        }

                        events.push(event);
                    });

                    successCallback(events);
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

            // Buscar si ese día tiene algún evento
            const hasEvent = calendar.getEvents().some(event => {
                const startDate = event.startStr.split('T')[0];
                const endDate = event.endStr ? event.endStr.split('T')[0] : startDate;
                return dateStr >= startDate && dateStr <= endDate;
            });

            // Si es sábado o domingo y no tiene cursos, poner fondo gris clarito
            if ((day === 0 || day === 6) && !hasEvent) {
                info.el.style.backgroundColor = '#f8f9fa';
            }
        }
    });

    calendar.render();
});
