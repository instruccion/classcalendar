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
import './bootstrap'; // Asegúrate que la ruta sea correcta
import './layout.js'; // Asegúrate que la ruta sea correcta

// 🛠️ Importar calendario.js SOLO si NO estamos en /mi-agenda
document.addEventListener('DOMContentLoaded', function () {
    if (!window.location.pathname.includes('/mi-agenda')) {
        import('./calendario.js')
            .then(() => {
                console.log('calendario.js cargado');
            })
            .catch((err) => {
                console.error('Error al cargar calendario.js', err);
            });
    }
});

console.log('app.js cargado y FullCalendar/Alpine configurados.'); // Log de verificación

// Filtrar los grupos en el modal de editar cursos
function abrirModalEditarCurso(id) {
    fetch(`/admin/cursos/${id}/edit`)
        .then(res => res.json())
        .then(data => {
            // Rellenar grupos dinámicamente
            const grupoContainer = document.getElementById('curso_edit_grupos');
            grupoContainer.innerHTML = ''; // Limpiar anteriores

            data.todos_grupos.forEach(grupo => {
                const label = document.createElement('label');
                label.className = 'inline-flex items-center gap-2 text-sm';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'grupo_ids[]';
                checkbox.value = grupo.id;
                checkbox.className = 'form-checkbox h-4 w-4 text-indigo-600';
                if (data.grupo_ids.includes(grupo.id)) {
                    checkbox.checked = true;
                }

                label.appendChild(checkbox);
                label.appendChild(document.createTextNode(grupo.nombre));
                grupoContainer.appendChild(label);
            });

            // Rellenar demás campos
            document.getElementById('curso_edit_nombre').value = data.nombre;
            document.getElementById('curso_edit_tipo').value = data.tipo;
            document.getElementById('curso_edit_duracion').value = data.duracion_horas;
            document.getElementById('curso_edit_descripcion').value = data.descripcion;
            document.getElementById('curso_edit_id').value = data.id;

            document.getElementById('modalEditarCurso').showModal();
        });
}
