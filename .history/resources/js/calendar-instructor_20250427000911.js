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

    const diasConCursos = new Set();

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

                    diasConCursos.clear();

                    data.forEach(event => {
                        events.push(event);
                        if (event.start) {
                            const fecha = event.start.split('T')[0];
                            diasConCursos.add(fecha);
                        }
                    });

                    successCallback(events);
                })
                .catch(error => {
                    console.error('Error al cargar eventos:', error);
                    failureCallback(error);
                });
        },

        // PINTA fondo gris sábados y domingos en vista MES
        dayCellDidMount(info) {
            const day = info.date.getDay();
            if (day === 0 || day === 6) {
                info.el.style.backgroundColor = '#f8f9fa';
            }
        },

        // PINTA fondo gris sábados y domingos en vista SEMANA
        slotLaneDidMount(info) {
            const day = info.date.getDay();
            if (day === 0 || day === 6) {
                info.el.style.backgroundColor = '#f8f9fa';
            }
        },

        eventClick: function (info) {
            const props = info.event.extendedProps;
            if (info.event.id && info.event.id.startsWith('almuerzo')) return;

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

        viewDidMount(viewInfo) {
            // 🔥 SOLO si es semana
            if (calendar.view.type === 'timeGridWeek') {
                const almuerzos = [];

                diasConCursos.forEach(fecha => {
                    almuerzos.push({
                        id: 'almuerzo-' + fecha,
                        title: '🍴 Almuerzo',
                        start: fecha + 'T12:00:00',
                        end: fecha + 'T13:00:00',
                        display: 'background', // << IMPORTANTE
                        backgroundColor: '#d1d5db',
                        textColor: '#374151'
                    });
                });

                // Primero removemos las fuentes anteriores de almuerzo si existen
                calendar.getEventSources().forEach(source => {
                    if (source.internalEventSource?.meta === 'almuerzo') {
                        source.remove();
                    }
                });

                // Agregamos nueva fuente
                calendar.addEventSource({
                    events: almuerzos,
                    meta: 'almuerzo'
                });
            }
        }
    });

    calendar.render();
});
