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

    slotMinTime: "08:00:00",
    slotMaxTime: "18:00:00",

    businessHours: [
        { daysOfWeek: [1, 2, 3, 4, 5], startTime: '08:30', endTime: '12:00' },
        { daysOfWeek: [1, 2, 3, 4, 5], startTime: '13:00', endTime: '17:00' }
    ],

    weekends: false, // NO mostrar sábados y domingos
    // Agregamos eventos de fondo fijos para bloqueos
    eventSources: [
        {
            events: [
                {
                    id: 'almuerzo',
                    daysOfWeek: [1, 2, 3, 4, 5],
                    startTime: '12:00',
                    endTime: '13:00',
                    display: 'background',
                    color: '#f0f0f0'
                }
            ],
        }
    ]
});
