<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            📅 Agenda de {{ $selectedInstructor ? $selectedInstructor->nombre : 'Instructores' }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8"> {{-- Añadido padding responsivo --}}
        {{-- Selector de Instructor --}}
        <div class="bg-white shadow sm:rounded-lg p-4"> {{-- Envuelto en card --}}
            <form method="GET" action="{{ route('admin.instructores.agenda') }}" class="max-w-md">
                <label for="instructor_id" class="block font-medium text-sm text-gray-700 mb-1">Selecciona un instructor:</label>
                <select name="instructor_id" id="instructor_id" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">-- Elegir instructor --</option>
                    @foreach($instructores as $inst)
                        <option value="{{ $inst->id }}" @selected(request('instructor_id') == $inst->id)> {{-- Usar @selected --}}
                            {{ $inst->nombre }}
                        </option>
                    @endforeach
                </select>
                 {{-- Añadir botón para limpiar selección si se desea --}}
                 @if(request('instructor_id'))
                    <a href="{{ route('admin.instructores.agenda') }}" class="text-sm text-gray-600 hover:text-gray-900 mt-1 inline-block">Limpiar selección</a>
                 @endif
            </form>
        </div>

        {{-- Tabla de Programaciones (Solo si hay instructor seleccionado) --}}
        @if($instructor_id && $programaciones->count())
            <div class="bg-white shadow sm:rounded-lg p-4 overflow-x-auto">
                <h3 class="font-semibold text-lg mb-3 text-gray-800">Cursos asignados a {{ $selectedInstructor->nombre }}</h3>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider"> {{-- Estilo thead --}}
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left">Curso</th>
                            <th scope="col" class="px-4 py-2 text-left">Grupo</th>
                            <th scope="col" class="px-4 py-2 text-left">Inicio</th>
                            <th scope="col" class="px-4 py-2 text-left">Fin</th>
                            <th scope="col" class="px-4 py-2 text-left">Horario</th>
                            <th scope="col" class="px-4 py-2 text-left">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200"> {{-- Estilo tbody --}}
                        @foreach ($programaciones as $p)
                            <tr class="hover:bg-gray-50"> {{-- Hover effect --}}
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->curso->nombre ?? '—' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->grupo->nombre ?? '—' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->fecha_inicio?->format('d/m/Y') ?? 'N/A' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->fecha_fin?->format('d/m/Y') ?? 'N/A' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->hora_inicio ? substr($p->hora_inicio, 0, 5) : '' }} - {{ $p->hora_fin ? substr($p->hora_fin, 0, 5) : '' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <span @class([
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $p->estado_confirmacion === 'confirmado',
                                        'bg-red-100 text-red-800' => $p->estado_confirmacion === 'rechazado',
                                        'bg-yellow-100 text-yellow-800' => $p->estado_confirmacion !== 'confirmado' && $p->estado_confirmacion !== 'rechazado',
                                    ])>
                                        {{ ucfirst($p->estado_confirmacion ?? 'pendiente') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($instructor_id)
             <div class="bg-white shadow sm:rounded-lg p-4">
                 <p class="text-center text-gray-500">No hay cursos asignados para {{ $selectedInstructor->nombre }}.</p>
             </div>
        @else
             <div class="bg-white shadow sm:rounded-lg p-4">
                 <p class="text-center text-gray-500">Selecciona un instructor para ver su agenda.</p>
             </div>
        @endif

        {{-- Calendario (Solo si hay instructor seleccionado) --}}
        @if($instructor_id)
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div id='calendar-container' wire:ignore> {{-- Añadido wire:ignore si usas Livewire en algún punto --}}
                     <div id='calendar'></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal de Detalles (usando <dialog>) --}}
    <dialog id="modalDetalle" class="rounded-lg shadow-xl p-0 w-full max-w-lg overflow-hidden">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center mb-4">
                 <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Detalles del Curso</h3>
                 <button onclick="document.getElementById('modalDetalle').close()" class="text-gray-400 hover:text-gray-600">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>
            </div>

            <div id="modalContent" class="text-sm text-gray-700 space-y-2">
                {{-- El contenido se inyectará aquí --}}
                <p>Cargando...</p>
            </div>
            <div class="mt-6 text-right">
                <button onclick="document.getElementById('modalDetalle').close()" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cerrar
                </button>
            </div>
        </div>
    </dialog>

    @push('scripts') {{-- Empujar scripts al stack 'scripts' definido en app-layout --}}

    {{-- *** OPCIÓN 1: Si usas Vite/NPM para FullCalendar (RECOMENDADO) *** --}}
    {{-- 1. Asegúrate de haber corrido: npm install @fullcalendar/core @fullcalendar/daygrid @fullcalendar/timegrid @fullcalendar/list @fullcalendar/interaction --}}
    {{-- 2. Importa y configura en tu resources/js/app.js o un archivo específico --}}
    {{--    EJEMPLO en app.js:
            import { Calendar } from '@fullcalendar/core';
            import dayGridPlugin from '@fullcalendar/daygrid';
            import timeGridPlugin from '@fullcalendar/timegrid';
            import listPlugin from '@fullcalendar/list';
            import interactionPlugin from '@fullcalendar/interaction'; // Para dblClick
            window.FullCalendar = Calendar; // Hacer global si es necesario o pasar como módulo
            window.dayGridPlugin = dayGridPlugin;
            window.timeGridPlugin = timeGridPlugin;
            window.listPlugin = listPlugin;
            window.interactionPlugin = interactionPlugin;
            // NO inicialices el calendario aquí si es específico de la página
     --}}
     {{-- 3. Quita los <script> y <link> de CDN de abajo --}}

    {{-- *** OPCIÓN 2: Si usas CDN (ASEGÚRATE DE QUE NO HAYA CONFLICTOS CON app.js/calendario.js) *** --}}
     <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet"> {{-- CSS primero --}}
     <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.global.min.js" defer></script> {{-- JS con defer --}}
     {{-- Nota: main.global.min.js usualmente incluye interaction --}}

    <script>
        // Esperar a que el DOM esté listo Y FullCalendar esté disponible
        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            const instructorId = '{{ $instructor_id ?? '' }}'; // Obtener el ID actual del instructor desde PHP

            // Si no hay instructor seleccionado O el elemento #calendar no existe, no hacer nada
            if (!instructorId || !calendarEl) {
                 if (!instructorId) console.log("No hay instructor seleccionado, no se inicializa el calendario.");
                 if (!calendarEl) console.log("Elemento #calendar no encontrado.");
                 return;
            }

            // Verificar si FullCalendar está cargado (importante si usas CDN con defer o carga asíncrona)
             if (typeof FullCalendar === 'undefined') {
                console.error("FullCalendar no está cargado todavía.");
                // Reintentar después de un breve retraso
                setTimeout(initializeCalendar, 100);
                return;
             }
             console.log("FullCalendar cargado, inicializando...");


            // Definir la URL de la API usando route() de Laravel
            const eventsUrl = '{{ route("api.mi-agenda") }}';

            const calendar = new FullCalendar.Calendar(calendarEl, {
                // ----- Plugins (Si usas NPM/Vite y no los registraste globalmente) -----
                // plugins: [ dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin ],

                // ----- Configuración General -----
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' // Añadido timeGridDay
                },
                initialView: 'dayGridMonth',
                locale: 'es', // Para idioma español
                navLinks: true, // Permite hacer clic en nombres de días/semanas
                editable: false, // Eventos no se pueden arrastrar ni redimensionar
                selectable: false, // Días no se pueden seleccionar para crear eventos
                dayMaxEvents: true, // Muestra un "+X más" si hay muchos eventos
                weekends: true, // Mostrar fines de semana
                aspectRatio: 1.8, // Ajusta la proporción alto/ancho

                // ----- Fuentes de Eventos -----
                events: {
                    url: `${eventsUrl}?instructor_id=${instructorId}`, // Usar la URL generada
                    method: 'GET',
                    failure: function(error) {
                        console.error("Error al cargar eventos:", error);
                        alert('Hubo un error al cargar los eventos del calendario.');
                        // Aquí podrías mostrar un mensaje más amigable al usuario
                    },
                    success: function(data) {
                        console.log("Eventos cargados:", data);
                         // Puedes manipular 'data' aquí antes de que FullCalendar los procese si es necesario
                    },
                    // color: 'yellow',   // Color por defecto para todos los eventos de esta fuente
                    // textColor: 'black' // Color de texto por defecto
                },

                 // ----- Interacción y Visualización -----
                eventDisplay: 'block', // Muestra eventos como bloques sólidos
                eventTimeFormat: { // Formato de hora en vistas de tiempo
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short' // a.m./p.m.
                },
                displayEventEnd: true, // Muestra la hora de fin del evento

                // ----- Callbacks -----
                eventDidMount: function(info) {
                    // Añadir tooltip (requiere una librería como Tippy.js o usar title nativo)
                    info.el.setAttribute('title', `${info.event.title}\n${info.event.extendedProps.hora_inicio} - ${info.event.extendedProps.hora_fin}`);
                     // Puedes añadir clases aquí si necesitas
                     // console.log("Evento montado:", info.event);
                },

                eventDblClick: function(info) { // Cambiado a DOBLE CLICK
                    // Prevenir comportamiento por defecto si lo hubiera
                    info.jsEvent.preventDefault();

                    const props = info.event.extendedProps;
                    const event = info.event;
                    const modal = document.getElementById('modalDetalle');
                    const modalTitle = document.getElementById('modalTitle');
                    const contenido = document.getElementById('modalContent');

                    modalTitle.textContent = `Detalles: ${event.title}`; // Título dinámico

                    contenido.innerHTML = `
                        <p><strong><i class="fas fa-book mr-1 text-indigo-500"></i>Curso:</strong> ${event.title}</p>
                        <p><strong><i class="fas fa-users mr-1 text-indigo-500"></i>Grupo:</strong> ${props.grupo}</p>
                        <p><strong><i class="fas fa-chalkboard-teacher mr-1 text-indigo-500"></i>Aula:</strong> ${props.aula}</p>
                        <p><strong><i class="fas fa-calendar-alt mr-1 text-indigo-500"></i>Inicio:</strong> ${props.fecha_inicio_fmt} ${props.hora_inicio}</p>
                        <p><strong><i class="fas fa-calendar-check mr-1 text-indigo-500"></i>Fin:</strong> ${props.fecha_fin_fmt} ${props.hora_fin}</p>
                        <p><strong><i class="fas fa-check-circle mr-1 ${props.estado === 'confirmado' ? 'text-green-500' : (props.estado === 'rechazado' ? 'text-red-500' : 'text-yellow-500')}"></i>Estado:</strong> ${props.estadoDisplay}</p>
                    `;
                    // Añadir iconos de Font Awesome si los tienes configurados, o quita las <i>

                    // Mostrar el modal nativo
                    if (modal && typeof modal.showModal === 'function') {
                        modal.showModal();
                    } else {
                        console.error("El elemento modal no existe o no soporta showModal().");
                         // Fallback si showModal no funciona (muy raro en navegadores modernos)
                         // modal.style.display = 'block'; // O una clase CSS para mostrarlo
                    }
                },

                 // Callback cuando el calendario se renderiza por primera vez o cambia de vista/fecha
                 datesSet: function(dateInfo) {
                     console.log("Vista del calendario actualizada:", dateInfo.view.type, dateInfo.startStr, dateInfo.endStr);
                     // Útil para depuración o cargar datos adicionales si fuera necesario
                 }

            }); // Fin new Calendar

             // Renderizar el calendario
            calendar.render();
             console.log("Calendario renderizado.");

             // Guardar referencia al calendario si necesitas acceder desde fuera (p.ej. consola)
             window.instructorCalendar = calendar;

        } // Fin initializeCalendar()

        // Ejecutar la inicialización después de que todo esté cargado
        // Si usas CDN con defer, DOMContentLoaded puede ser suficiente
        // Si tienes problemas de timing, window.onload es más seguro pero más lento
         document.addEventListener('DOMContentLoaded', initializeCalendar);
        // Alternativa más segura si sigue fallando el constructor:
        // window.addEventListener('load', initializeCalendar);

    </script>
     {{-- Asegúrate de que Font Awesome esté cargado si usaste los iconos <i> --}}
    @endpush

</x-app-layout>
