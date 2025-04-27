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
        events: async function (info, successCallback, failureCallback) {
            try {
                const response = await fetch(`${window.location.origin}/cursoslaser/public/index.php/api/instructor-agenda?instructor_id=${instructorId}`);
                const eventosPrincipales = await response.json();

                // Verificamos si estamos en vista semana
                const esSemana = info.view.type === 'timeGridWeek';
                const diasConEventos = new Set(eventosPrincipales.map(e => new Date(e.start).toISOString().slice(0,10)));

                let eventosAlmuerzo = [];

                if (esSemana) {
                    // Solo si es vista de semana agregamos bloques de almuerzo
                    eventosAlmuerzo = [...diasConEventos].map(dia => ({
                        title: 'Almuerzo',
                        start: `${dia}T12:00:00`,
                        end: `${dia}T13:00:00`,
                        display: 'background',
                        color: '#d1d5db', // gris claro
                        textColor: '#111827'
                    }));
                }

                successCallback([...eventosPrincipales, ...eventosAlmuerzo]);
            } catch (error) {
                console.error('Error cargando eventos:', error);
                failureCallback(error);
            }
        },
        eventClick: function (info) {
            const props = info.event.extendedProps;
            const modal = document.getElementById('modalDetalle');
            const contenido = `
                <p><strong>Curso:</strong> ${info.event.title}</p>
                <p><strong>Grupo:</strong> ${props.grupo ?? '—'}</p>
                <p><strong>Aula:</strong> ${props.aula ?? '—'}</p>
                <p><strong>Fecha Inicio:</strong> ${props.fecha_inicio_fmt ?? '—'}</p>
                <p><strong>Fecha Fin:</strong> ${props.fecha_fin_fmt ?? '—'}</p>
                <p><strong>Horario:</strong> ${props.hora_inicio ?? '—'} - ${props.hora_fin ?? '—'}</p>
                <p><strong>Estado:</strong> ${props.estadoDisplay ?? '—'}</p>
            `;
            document.getElementById('modalContent').innerHTML = contenido;
            modal.showModal();
        },
        eventDisplay: 'block',
    });

    calendar.render();
});
