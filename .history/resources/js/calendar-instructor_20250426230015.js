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
        events: async function (info, successCallback, failureCallback) {
            try {
                const res = await fetch(`${window.location.origin}/cursoslaser/public/index.php/api/instructor-agenda?instructor_id=${instructorId}`);
                const eventosCursos = await res.json();

                const esSemana = info.view.type === 'timeGridWeek';
                let eventosFinales = [...eventosCursos];

                if (esSemana) {
                    const diasConCurso = new Set(eventosCursos.map(ev => ev.start.substring(0, 10)));

                    for (const dia of diasConCurso) {
                        eventosFinales.push({
                            title: 'Almuerzo',
                            start: `${dia}T12:00:00`,
                            end: `${dia}T13:00:00`,
                            display: 'background',
                            backgroundColor: '#d1d5db',
                            textColor: '#111827'
                        });
                    }
                }

                successCallback(eventosFinales);
            } catch (error) {
                console.error('Error cargando eventos', error);
                failureCallback(error);
            }
        },
        eventClick: function (info) {
            // Si clickeas sobre un background event (almuerzo) no abre modal
            if (info.event.display === 'background') return;

            const props = info.event.extendedProps;
            const modal = document.getElementById('modalDetalle');
            const contenido = `
                <p><strong>Curso:</strong> ${info.event.title}</p>
                <p><strong>Grupo:</strong> ${props?.grupo ?? '—'}</p>
                <p><strong>Aula:</strong> ${props?.aula ?? '—'}</p>
                <p><strong>Fecha Inicio:</strong> ${props?.fecha_inicio_fmt ?? '—'}</p>
                <p><strong>Fecha Fin:</strong> ${props?.fecha_fin_fmt ?? '—'}</p>
                <p><strong>Horario:</strong> ${props?.hora_inicio ?? '—'} - ${props?.hora_fin ?? '—'}</p>
                <p><strong>Estado:</strong> ${props?.estadoDisplay ?? '—'}</p>
            `;
            document.getElementById('modalContent').innerHTML = contenido;
            modal.showModal();
        },
        eventDisplay: 'block'
    });

    calendar.render();
});
