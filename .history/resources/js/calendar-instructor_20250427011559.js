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
        events: window.location.origin + '/cursoslaser/public/index.php/api/instructor-agenda?instructor_id=' + instructorId,

        eventClick: function (info) {
            const props = info.event.extendedProps;

            // No abrir modal para almuerzo
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

        dayCellDidMount: function (info) {
            const day = info.date.getDay();
            if (day === 0 || day === 6) {
                info.el.style.backgroundColor = '#f8f9fa'; // gris para sábado y domingo
            }
        },

        viewDidMount: function (arg) {
            if (arg.view.type === 'timeGridWeek') {
                // Solo si estamos en vista Semana agregamos Almuerzos
                agregarEventosAlmuerzo(calendar);
            }
        },
    });

    calendar.render();
});

// 👇 Función adicional para agregar los bloques de almuerzo
function agregarEventosAlmuerzo(calendar) {
    const currentStart = calendar.view.currentStart;
    const currentEnd = calendar.view.currentEnd;
    const almuerzoEventos = [];

    let current = new Date(currentStart);
    while (current <= currentEnd) {
        const day = current.getDay();
        // Solo agregar si es lunes a viernes (1-5)
        if (day >= 1 && day <= 5) {
            const fechaStr = current.toISOString().split('T')[0];
            almuerzoEventos.push({
                id: 'almuerzo-' + fechaStr,
                title: '🍴 Almuerzo',
                start: fechaStr + 'T12:00:00',
                end: fechaStr + 'T13:00:00',
                display: 'background',
                backgroundColor: '#d1d5db',
                borderColor: '#d1d5db',
                textColor: '#4b5563'
            });
        }
        current.setDate(current.getDate() + 1);
    }

    calendar.addEventSource(almuerzoEventos);
}
