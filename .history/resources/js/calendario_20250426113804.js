document.addEventListener('DOMContentLoaded', () => {
    const mainCalendarEl = document.getElementById('calendar');
    const instructorCalendarEl = document.getElementById('instructor-agenda-calendar');

    if (mainCalendarEl) {
        // (tu código que ya tienes para el calendario principal)
    }

    if (instructorCalendarEl) {
        console.log('Agenda de Instructor encontrada. Inicializando...');

        const instructorId = document.getElementById('instructor_id')?.value;

        if (!instructorId) {
            console.warn('No hay instructor seleccionado.');
            return;
        }

        const calendar = new Calendar(instructorCalendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            locale: esLocale,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            events: {
                url: `/cursoslaser/public/index.php/api/mi-agenda`,
                method: 'GET',
                extraParams: {
                    instructor_id: instructorId
                },
                failure: function() {
                    alert('Error al cargar eventos del instructor.');
                }
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
    }
});
