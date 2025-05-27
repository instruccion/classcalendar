import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('instructor-agenda-calendar');

    console.log('calendar-mi-agenda.js INICIANDO...');

    if (!calendarEl) {
        console.warn('No hay calendario disponible.');
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
        eventDisplay: 'block',

        events: function(fetchInfo, successCallback, failureCallback) {
            const url = `${window.location.origin}/cursoslaser/public/index.php/api/mi-agenda`;

            console.log('Llamando a:', url);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log('DATA RECIBIDA de /api/mi-agenda:', data);
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
                    console.error('Error al cargar eventos de Mi Agenda:', error);
                    failureCallback(error);
                });
        },

        eventClick: function(info) {
            const props = info.event.extendedProps;
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
            document.getElementById('modalDetalle').showModal();
        }
    });

    calendar.render();
});
