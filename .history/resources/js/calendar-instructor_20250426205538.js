document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('instructor-agenda-calendar');
    const instructorId = document.getElementById('instructor_id')?.value;

    if (!calendarEl || !instructorId) {
        console.log('No hay calendario o instructor.');
        return;
    }

    console.log('Inicializando Calendar para instructor:', instructorId);

    const calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: esLocale,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        slotMinTime: "08:00:00", // <-- Mostramos desde las 08:00 am
        slotMaxTime: "18:00:00", // <-- Hasta las 6:00 pm
        businessHours: [         // <-- Definimos las horas académicas reales
            {
                daysOfWeek: [1, 2, 3, 4, 5], // Lunes a Viernes
                startTime: '08:30',
                endTime: '12:00'
            },
            {
                daysOfWeek: [1, 2, 3, 4, 5], // Lunes a Viernes
                startTime: '13:00',
                endTime: '17:00'
            }
        ],
        events: {
            url: '/cursoslaser/public/index.php/api/instructor-agenda',
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
        eventDisplay: 'block'
    });

    calendar.render();
});
