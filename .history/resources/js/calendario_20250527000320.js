import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

document.addEventListener('DOMContentLoaded', () => {
    // Busca el contenedor del calendario principal
    const calendarEl = document.getElementById('calendar');
    // Busca los selectores de filtro (pueden no existir en todas las páginas)
    const grupoSelect = document.getElementById('grupo');
    const coordSelect = document.getElementById('coordinacion');

    // --- INICIO: CONDICIÓN PARA EJECUTAR SOLO SI EXISTE EL CALENDARIO ---
    if (calendarEl) {
        console.log('Calendario principal (#calendar) encontrado. Inicializando...'); // Log para depuración

        let lastClick = null;

        // Crear la instancia del calendario principal
        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            locale: esLocale, // Usar el locale importado
            height: 'auto',
            expandRows: true,
            fixedWeekCount: false,
            dayMaxEventRows: true,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            events: buildApiUrl(), // Carga inicial de eventos
            dateClick: function(info) {
                const now = Date.now();
                if (lastClick && now - lastClick.time < 400 && lastClick.date === info.dateStr) {
                    // Aquí podrías abrir un modal para añadir evento en esa fecha, por ejemplo
                    window.location.href = `admin/programaciones/create?fecha=${info.dateStr}`; // Ejemplo: Ir a programar
                    // alert('📌 Doble clic en: ' + info.dateStr);
                    lastClick = null;
                } else {
                    lastClick = { time: now, date: info.dateStr };
                }
            },
            // Podrías añadir eventClick aquí también si quieres hacer algo al clicar un evento existente
            // eventClick: function(info) {
            //    alert('Evento: ' + info.event.title);
            //    // Aquí podrías abrir un modal para editar/ver detalles:
            //    // window.location.href = `admin/programaciones/${info.event.id}/edit`;
            // }
        });

        // Renderizar el calendario principal
        calendar.render();

        


        // Observador para redimensionar (solo si el calendario existe)
        const observer = new ResizeObserver(() => {
            calendar.updateSize();
        });
        const container = calendarEl.parentElement; // Obtener padre del elemento calendario
        if (container) {
            observer.observe(container);
        }

        // --- Funciones para recargar y construir URL (solo necesarias si el calendario existe) ---
        function reloadCalendar() {
            const url = buildApiUrl();
            // console.log('Recargando eventos desde:', url); // Log opcional
            // calendar.removeAllEvents(); // No siempre es necesario quitar todos primero
            calendar.setOption('events', url); // Cambia la fuente de eventos
            // calendar.refetchEvents(); // setOption con 'events' suele ser suficiente
        }

        function buildApiUrl() {
            const grupo = grupoSelect?.value || '';
            const coor = coordSelect?.value || '';
            const params = new URLSearchParams();
            if (grupo) params.append('grupo', grupo);
            if (coor) params.append('coordinacion', coor);
            return `${window.location.origin}/cursoslaser/public/api/eventos?${params.toString()}`;
        }

        // Añadir listeners a los filtros (solo si existen)
        if (grupoSelect) {
            grupoSelect.addEventListener('change', reloadCalendar);
        }
        if (coordSelect) {
            coordSelect.addEventListener('change', reloadCalendar);
        }

    } else {
        // Log opcional para saber que no se inicializó en esta página
         console.log('Contenedor #calendar no encontrado, no se inicializa el calendario principal.');
    }
    // --- FIN: CONDICIÓN ---

}); // Fin del addEventListener DOMContentLoaded
