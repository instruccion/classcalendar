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

    console.log('Inicializando Calendar para instructor:', instructorId);

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
        businessHours: [
            { daysOfWeek: [1,2,3,4,5], startTime: '08:30', endTime: '12:00' },
            { daysOfWeek: [1,2,3,4,5], startTime: '13:00', endTime: '17:00' }
        ],
        events: {
            url: window.location.origin + '/cursoslaser/public/index.php/api/instructor-agenda',
            method: 'GET',
            extraParams: {
                instructor_id: instructorId
            },
            failure: function () {
                alert('Error cargando eventos.');
            }
        },
        eventSources: [
            {
                events: [
                    // Bloque visual de almuerzo
                    {
                        id: 'almuerzo',
                        title: '🍴 Almuerzo',
                        daysOfWeek: [1,2,3,4,5], // Lunes a viernes
                        startTime: '12:00',
                        endTime: '13:00',
                        display: 'block', // IMPORTANTE: mostrar como evento visible
                        backgroundColor: '#d1d5db', // gris claro
                        borderColor: '#9ca3af', // borde gris más fuerte
                        textColor: '#374151' // texto más oscuro
                    }
                ]
            }
        ],
        dayCellDidMount(info) {
            const day = info.date.getDay();
            if (day === 0 || day === 6) {
                info.el.style.backgroundColor = '#f8f9fa'; // color clarito sábados y domingos
            }
        },
        eventClick: function (info) {
            const props = info.event.extendedProps;

            // No abrir modal para almuerzo
            if (info.event.id === 'almuerzo') return;

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
