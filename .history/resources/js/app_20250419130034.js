import './bootstrap';
import Alpine from 'alpinejs';
import './calendario.js';
import './layout.js';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

window.FullCalendar = Calendar;
window.dayGridPlugin = dayGridPlugin;
window.interactionPlugin = interactionPlugin;
window.esLocale = esLocale;

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
