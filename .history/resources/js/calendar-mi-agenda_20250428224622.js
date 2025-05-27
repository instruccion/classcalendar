import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('instructor-agenda-calendar');
    const instructorId = window.instructorActualId;

    console.log('calendar-mi-agenda.js INICIANDO...');

    if (!calendarEl || !instructorId) {
        console.log('No hay calendario o instructor en Mi Agenda.');
        return;
    }

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: esLocale,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch(window.location.origin + '/cursoslaser/public/index.php/api/mi-agenda?instructor_id=' + instructorId)
                .then(response => response.json())
                .then(data => {
                    console.log('DATA RECIBIDA de /api/mi-agenda:', data); // ⬅️ AGREGA ESTA LÍNEA

                    const eventos = data.map(evento => ({
                        title: evento.title,
                        start: evento.start,
                        end: evento.end,
                        color: evento.color,
                        borderColor: evento.borderColor,
                        extendedProps: evento.extendedProps
                    }));

                    successCallback(eventos);
                })
                .catch(error => {
                    console.error('Error al cargar eventos:', error);
                    failureCallback(error);
                });
        },

        eventClick: function(info) {
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
        eventDisplay: 'block'
    });

    calendar.render();
});
