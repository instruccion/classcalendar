import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');
    const grupoSelect = document.getElementById('grupo');
    const coordSelect = document.getElementById('coordinacion');

    let lastClick = null;

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: esLocale,
        height: 'auto',
        expandRows: true,
        fixedWeekCount: false,
        dayMaxEventRows: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        events: buildApiUrl(),
        dateClick: function(info) {
            const now = Date.now();
            if (lastClick && now - lastClick.time < 400 && lastClick.date === info.dateStr) {
                alert('📌 Doble clic en: ' + info.dateStr);
                lastClick = null;
            } else {
                lastClick = { time: now, date: info.dateStr };
            }
        }
    });

    calendar.render();

    // Recargar eventos al cambiar filtros
    function reloadCalendar() {
        const url = buildApiUrl();
        calendar.removeAllEvents();
        calendar.setOption('events', url);
        calendar.refetchEvents();
    }

    function buildApiUrl() {
        const grupo = grupoSelect?.value || '';
        const coor = coordSelect?.value || '';
        const params = new URLSearchParams();
        if (grupo) params.append('grupo', grupo);
        if (coor) params.append('coordinacion', coor);
        return `/api/eventos?${params.toString()}`;
    }

    grupoSelect?.addEventListener('change', reloadCalendar);
    coordSelect?.addEventListener('change', reloadCalendar);

    // 🔄 Ajuste automático del tamaño del calendario al esconder el sidebar
    const sidebarToggleBtn = document.getElementById('menu-toggle');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', () => {
            setTimeout(() => {
                calendar.updateSize(); // <- FullCalendar recalcula
            }, 310); // espera a que la animación termine (300ms + margen)
        });
    }

});
