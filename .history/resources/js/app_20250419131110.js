// Importar CSS de FullCalendar
import '@fullcalendar/core/main.css';
import '@fullcalendar/daygrid/main.css';
// Añade aquí imports de CSS de otros plugins si los instalaste

// Importar JS de FullCalendar y plugins
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

// Hacerlos disponibles globalmente para Alpine
window.FullCalendar = Calendar;
window.dayGridPlugin = dayGridPlugin;
window.interactionPlugin = interactionPlugin;
window.esLocale = esLocale;

// Importar Alpine (SOLO UNA VEZ)
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Importar otros JS tuyos
import './bootstrap'; // Asegúrate que la ruta sea correcta si lo usas
import './calendario.js'; // Asegúrate que la ruta sea correcta
import './layout.js'; // Asegúrate que la ruta sea correcta

console.log('app.js cargado y FullCalendar/Alpine configurados.'); // Log de verificación
