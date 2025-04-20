<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar Nuevo Curso
        </h2>
    </x-slot>

    {{-- Incluir FullCalendar CSS (si aún no está en el layout principal) --}}
    {{-- <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.14/main.min.css' rel='stylesheet' /> --}}
    {{-- <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.14/main.min.css' rel='stylesheet' /> --}}
    {{-- <link href="{{ asset('assets/css/minicalendar_custom.css') }}" rel="stylesheet"> --}}

    <div class="py-6 max-w-4xl mx-auto">
        {{-- Botón Volver (Opcional) --}}
        {{-- <a href="{{ route('admin.programaciones.index') }}" class="inline-flex items-center text-blue-600 hover:underline mb-4">
            <i class="mdi mdi-arrow-left mr-2 text-xl"></i> Volver al Listado
        </a> --}}

        {{-- <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js'></script> --}}
        {{-- <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/locales/es.js'></script> --}}
        <div class="bg-white p-6 rounded shadow-md"
             x-data="programacionForm({
                 grupos: {{ Js::from($grupos) }},
                 instructoresActivos: {{ Js::from($instructores) }},
                 aulasActivas: {{ Js::from($aulas) }},
                 feriados: {{ Js::from($feriados) }},
                 rutasApi: {
                     cursosPorGrupo: '{{ route('admin.api.programaciones.cursosPorGrupo', ['grupo' => ':grupoId']) }}',
                     instructoresPorCurso: '{{ route('admin.api.programaciones.instructoresPorCurso', ['curso' => ':cursoId']) }}',
                     calcularFechaFin: '{{ route('admin.api.programaciones.calcularFechaFin') }}',
                     verificarDisponibilidad: '{{ route('admin.api.programaciones.verificarDisponibilidad') }}',
                     detalleDisponibilidad: '{{ route('admin.api.programaciones.detalleDisponibilidad') }}'
                 },
                 csrfToken: '{{ csrf_token() }}'
             })">

            <div class="flex justify-between items-center mb-6 pb-2 border-b">
                <h1 class="text-2xl font-bold">Programar Curso</h1>
                <a href="{{ route('admin.programaciones.bloque.show') }}"
                   class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">
                    📦 Programar por Bloque
                </a>
            </div>

            {{-- Mostrar errores de validación generales --}}
            <x-validation-errors class="mb-4" />

            <form :action="formAction" method="POST" id="form-programacion" @submit.prevent="submitForm"
                  class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                @csrf
                {{-- Campo oculto para el método (se añadirá PUT para editar si es necesario) --}}
                <input type="hidden" name="_method" x-bind:value="formMethod">

                <!-- Grupo -->
                <div>
                    <label for="grupo_id" class="block font-semibold mb-1">Grupo <span class="text-red-500">*</span></label>
                    <select name="grupo_id" id="grupo_id" required x-model="selectedGroupId" @change="loadCourses"
                            class="w-full border px-4 py-2 rounded bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Seleccione un grupo...</option>
                        <template x-for="grupo in grupos" :key="grupo.id">
                            <option :value="grupo.id" x-text="`${grupo.nombre} (${grupo.coordinacion.nombre})`"></option>
                        </template>
                    </select>
                </div>

                <!-- Curso -->
                <div>
                    <label for="curso_id" class="block font-semibold mb-1">Curso <span class="text-red-500">*</span></label>
                    <select name="curso_id" id="curso_id" required x-model="selectedCourseId" @change="handleCourseChange"
                            :disabled="isLoadingCourses || !selectedGroupId"
                            class="w-full border px-4 py-2 rounded bg-white focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100">
                        <option value="" x-show="!selectedGroupId">Seleccione un grupo primero...</option>
                        <option value="" x-show="selectedGroupId && !isLoadingCourses && availableCourses.length === 0">No hay cursos para este grupo...</option>
                        <option value="" x-show="selectedGroupId && isLoadingCourses">Cargando cursos...</option>
                        <template x-for="curso in availableCourses" :key="curso.id">
                            <option :value="curso.id" :data-duracion="curso.duracion_horas" x-text="curso.nombre"></option>
                        </template>
                    </select>
                    <input type="hidden" name="duracion_horas" x-bind:value="selectedCourseDuration">
                </div>

                <!-- Bloque -->
                <div class="md:col-span-2 flex items-center gap-4 border-t pt-4 mt-2">
                    <label for="usa_bloque" class="font-semibold flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="usa_bloque" x-model="useBlockCode"
                               class="form-checkbox text-blue-600 h-5 w-5 rounded focus:ring-blue-500">
                        ¿Pertenece a un bloque?
                    </label>
                    <input type="text" name="bloque_codigo" id="bloque_codigo" x-model="blockCode"
                           :disabled="!useBlockCode"
                           class="border px-4 py-2 rounded w-full md:w-1/3 disabled:bg-gray-100 disabled:cursor-not-allowed"
                           placeholder="Código de bloque (opcional)">
                </div>

                <!-- Fechas y Horas -->
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-4 border-t pt-4 mt-2">
                    <div>
                        <label for="fecha_inicio" class="block font-semibold mb-1">Fecha Inicio <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" required x-model="startDate"
                               @change="calculateEndDateAndCheckAvailability"
                               class="w-full border px-4 py-2 rounded focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="hora_inicio" class="block font-semibold mb-1">Hora Inicio <span class="text-red-500">*</span></label>
                        <input type="time" name="hora_inicio" id="hora_inicio" required x-model="startTime"
                               @change="checkAvailability('instructor'); checkAvailability('aula')"
                               class="w-full border px-4 py-2 rounded focus:border-indigo-500 focus:ring-indigo-500" value="08:30">
                    </div>
                     <div>
                        <label for="fecha_fin" class="block font-semibold mb-1">Fecha Fin (Estimada)</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" x-model="endDate" readonly
                               class="w-full border px-4 py-2 rounded bg-gray-100 cursor-not-allowed">
                    </div>
                     <div>
                        <label for="hora_fin" class="block font-semibold mb-1">Hora Fin (Estimada)</label>
                        <input type="time" name="hora_fin" id="hora_fin" x-model="endTime" readonly
                               class="w-full border px-4 py-2 rounded bg-gray-100 cursor-not-allowed">
                    </div>
                </div>

                 <!-- Aula -->
                 <div class="border-t pt-4 mt-2">
                    <label for="aula_id" class="block font-semibold mb-1">Aula <span class="text-red-500">*</span></label>
                    <div class="flex gap-2 items-center">
                        <select name="aula_id" id="aula_id" required x-model="selectedAulaId" @change="checkAvailability('aula')"
                                class="w-full border px-4 py-2 rounded bg-white focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500 text-red-600 font-bold': aulaOccupied }">
                            <option value="">Seleccione un aula...</option>
                            <template x-for="aula in aulasActivas" :key="aula.id">
                                <option :value="aula.id" x-text="aula.nombre + (aula.lugar ? ` – ${aula.lugar}` : '')"></option>
                            </template>
                        </select>
                        <button type="button" @click="openAvailabilityModal('aula')" :disabled="!selectedAulaId"
                                class="text-blue-600 hover:text-blue-800 text-xl p-1 disabled:text-gray-400 disabled:cursor-not-allowed"
                                title="Ver disponibilidad del aula">
                            <i class="mdi mdi-calendar-search"></i>
                        </button>
                    </div>
                    <div id="msg-aula" class="text-sm mt-1 text-red-600" x-show="aulaOccupied" x-cloak>
                        ⚠️ Esta aula ya está ocupada en las fechas/horas seleccionadas.
                    </div>
                </div>

                <!-- Instructor -->
                <div class="border-t pt-4 mt-2">
                    <label for="instructor_id" class="block font-semibold mb-1">Instructor <span class="text-red-500">*</span></label>
                    <div class="flex gap-2 items-center">
                         <select name="instructor_id" id="instructor_id" required x-model="selectedInstructorId" @change="checkAvailability('instructor')"
                                :disabled="isLoadingInstructors || !selectedCourseId"
                                class="w-full border px-4 py-2 rounded bg-white focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                :class="{ 'border-red-500 text-red-600 font-bold': instructorOccupied }">
                            <option value="" x-show="!selectedCourseId">Seleccione un curso primero...</option>
                            <option value="" x-show="selectedCourseId && isLoadingInstructors">Cargando instructores...</option>
                            <option value="" x-show="selectedCourseId && !isLoadingInstructors && availableInstructors.length === 0">No hay instructores para este curso...</option>
                            <template x-for="instructor in availableInstructors" :key="instructor.id">
                                <option :value="instructor.id" x-text="instructor.nombre"></option>
                            </template>
                        </select>
                         <button type="button" @click="openAvailabilityModal('instructor')" :disabled="!selectedInstructorId"
                                class="text-blue-600 hover:text-blue-800 text-xl p-1 disabled:text-gray-400 disabled:cursor-not-allowed"
                                title="Ver disponibilidad del instructor">
                             <i class="mdi mdi-calendar-search"></i>
                         </button>
                    </div>
                     <div id="msg-instructor" class="text-sm mt-1 text-red-600" x-show="instructorOccupied" x-cloak>
                        ⚠️ Este instructor ya está ocupado en las fechas/horas seleccionadas.
                    </div>
                </div>


                <!-- Botón de Envío -->
                <div class="md:col-span-2 text-center mt-6 pt-4 border-t">
                    <button type="submit"
                            :disabled="isLoadingEndDate || isLoadingCourses || isLoadingInstructors"
                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-wait">
                        <span x-show="!isLoadingEndDate">Confirmar Programación</span>
                        <span x-show="isLoadingEndDate">Calculando fecha fin...</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Modal de Disponibilidad --}}
        <dialog id="modalDisponibilidad" class="w-full max-w-4xl p-0 rounded-lg shadow-lg backdrop:bg-black/40">
             <div class="bg-white p-6 relative">
                <button @click="$el.closest('dialog').close()" class="absolute top-2 right-3 text-gray-600 hover:text-gray-900 text-2xl leading-none">×</button>
                <h2 class="text-xl font-bold mb-4" id="modalTitulo">Disponibilidad</h2>
                <div x-show="isLoadingAvailability" class="text-center py-10">Cargando disponibilidad...</div>
                <div x-show="!isLoadingAvailability" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Contenedor para FullCalendar --}}
                    <div id="mini-calendar" class="border rounded p-2 h-auto max-h-[450px] overflow-auto text-sm"></div>
                    {{-- Contenedor para la tabla de detalles --}}
                    <div id="tabla-detalle" class="overflow-y-auto max-h-[450px]">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2 mt-1">📋 Detalle de ocupación</h3>
                        {{-- La tabla se llenará con JS --}}
                        <div id="tabla-detalle-contenido">
                             <p class="text-gray-500 text-sm">Seleccione un recurso para ver detalles.</p>
                        </div>
                    </div>
                </div>
            </div>
        </dialog>

         {{-- Modal para detalles del evento del calendario --}}
        <dialog id="modalEventoDetalle" class="w-[90%] max-w-md p-0 rounded-lg shadow-lg backdrop:bg-black/40">
            <div class="bg-white p-6 text-center relative">
                 <h2 id="modalEventoTitulo" class="text-xl font-bold text-gray-800 mb-2">Detalle del Evento</h2>
                 <div class="flex items-center justify-center gap-3 mb-4">
                    <span id="modalEventoColor" class="w-4 h-4 rounded-full inline-block border border-gray-300 flex-shrink-0"></span>
                    <p id="modalEventoDescripcion" class="text-gray-700 text-sm text-left"></p>
                </div>
                 <button @click="$el.closest('dialog').close()" class="mt-4 bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400 transition">
                    Cerrar
                </button>
            </div>
        </dialog>

        {{-- Toast para fecha fin --}}
        <div id="toast-fecha-fin" x-show="showEndDateToast" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="fixed bottom-5 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50">
            📅 Fecha fin estimada: <span x-text="calculatedEndDateText"></span>
        </div>

    </div>

    {{-- Incluir FullCalendar JS (si aún no está en el layout principal) --}}
    {{-- <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/main.min.js'></script> --}}
    {{-- <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/locales/es.js'></script> --}}

    {{-- Lógica Alpine.js --}}
    <script>
        function programacionForm(config) {
            return {
                // --- Estado (sin cambios) ---
                grupos: config.grupos || [],
                instructoresActivos: config.instructoresActivos || [],
                aulasActivas: config.aulasActivas || [],
                feriados: config.feriados || [],
                rutasApi: config.rutasApi,
                csrfToken: config.csrfToken,
                selectedGroupId: '',
                selectedCourseId: '',
                selectedInstructorId: '',
                selectedAulaId: '',
                startDate: '',
                startTime: '08:30',
                endDate: '',
                endTime: '',
                useBlockCode: false,
                blockCode: '',
                availableCourses: [],
                availableInstructors: [],
                isLoadingCourses: false,
                isLoadingInstructors: false,
                isLoadingEndDate: false,
                isLoadingAvailability: false,
                instructorOccupied: false,
                aulaOccupied: false,
                showEndDateToast: false,
                calculatedEndDateText: '',
                formMethod: 'POST',
                formAction: "{{ route('admin.programaciones.store') }}",
                calendarInstance: null,

                // --- Métodos (sin cambios en init, selectedCourseDuration, loadCourses, handleCourseChange, loadInstructors, calculateEndDateAndCheckAvailability, calculateEndDate, checkAvailability) ---
                init() { console.log('Formulario inicializado.'); },
                get selectedCourseDuration() { /* ...código existente... */ },
                loadCourses() { /* ...código existente... */ },
                handleCourseChange() { /* ...código existente... */ },
                loadInstructors() { /* ...código existente... */ },
                calculateEndDateAndCheckAvailability() { /* ...código existente... */ },
                calculateEndDate() { /* ...código existente... */ },
                checkAvailability(type) { /* ...código existente... */ },


                // --- Modal de Disponibilidad (CON requestAnimationFrame) ---
                openAvailabilityModal(type) {
                    const resourceId = type === 'instructor' ? this.selectedInstructorId : this.selectedAulaId;
                    if (!resourceId) return;

                    const resourceName = document.querySelector(`#${type}_id option:checked`)?.textContent || `Recurso ID ${resourceId}`;
                    document.getElementById('modalTitulo').innerHTML = `Disponibilidad de <span class="capitalize font-bold text-gray-900">${type}</span> – <span class="font-bold text-blue-700">${resourceName}</span>`;

                    const modal = document.getElementById('modalDisponibilidad');
                    const calendarEl = document.getElementById('mini-calendar');
                    const tablaDetalleContenido = document.getElementById('tabla-detalle-contenido');

                    if (!modal || typeof modal.showModal !== 'function') {
                        console.error("ModalDisponibilidad: Elemento <dialog> no encontrado.");
                        alert("Error: No se puede abrir el modal.");
                        return;
                    }

                    modal.showModal();
                    this.isLoadingAvailability = true;
                    calendarEl.innerHTML = '';
                    tablaDetalleContenido.innerHTML = '<p class="text-gray-500 text-sm">Cargando detalles...</p>';

                    if (this.calendarInstance) {
                        this.calendarInstance.destroy();
                        this.calendarInstance = null;
                    }

                    const params = new URLSearchParams({ tipo: type, id: resourceId });
                    const url = `${this.rutasApi.detalleDisponibilidad}?${params.toString()}`;
                    console.log("ModalDisponibilidad: Llamando a API:", url);

                    fetch(url)
                        .then(res => {
                            console.log("ModalDisponibilidad: Respuesta API status:", res.status);
                            if (!res.ok) { return res.json().then(err => { throw new Error(err.error || `Error HTTP ${res.status}`) }); }
                            return res.json();
                        })
                        .then(data => {
                            console.log("ModalDisponibilidad: Datos JSON:", data);

                            // --- INICIO CAMBIO: Usar requestAnimationFrame ---
                            requestAnimationFrame(() => {
                                console.log("ModalDisponibilidad (rAF): Intentando renderizar...");
                                try {
                                    // Verificar FullCalendar global
                                    if (typeof window.FullCalendar === 'undefined' || !window.FullCalendar) {
                                        throw new Error('FullCalendar no está definido globalmente (window.FullCalendar). Revisa app.js.');
                                    }
                                    const currentCalendarEl = document.getElementById('mini-calendar');
                                    if (!currentCalendarEl) {
                                        throw new Error('Elemento #mini-calendar no encontrado.');
                                    }

                                    console.log("ModalDisponibilidad (rAF): Inicializando FullCalendar...");
                                    this.calendarInstance = new window.FullCalendar(currentCalendarEl, { // Usar window.FullCalendar
                                        plugins: [ window.dayGridPlugin, window.interactionPlugin ], // Usar plugins globales
                                        initialView: 'dayGridMonth',
                                        locale: window.esLocale, // Usar locale global
                                        height: 'auto',
                                        events: data.eventos || [],
                                        headerToolbar: { left: 'prev,next', center: 'title', right: '' },
                                        displayEventTime: false,
                                        eventClick: (info) => {
                                            const props = info.event.extendedProps;
                                            document.getElementById('modalEventoTitulo').textContent = info.event.title;
                                            document.getElementById('modalEventoColor').style.backgroundColor = props.color || '#9CA3AF';
                                            let descripcionHtml = `<p><strong>Grupo:</strong> ${props.grupo || 'N/A'}</p><p><strong>Fechas:</strong> ${props.fecha_inicio_fmt || '?'} - ${props.fecha_fin_fmt || '?'}</p><p><strong>Horario:</strong> ${props.hora_inicio_fmt || '?'} - ${props.hora_fin_fmt || '?'}</p><p><strong>${props.tipo_recurso_opuesto || 'Recurso'}:</strong> ${props.nombre_recurso_opuesto || 'N/A'}</p><p><strong>Coordinación:</strong> ${props.coordinacion || 'N/A'}</p>`;
                                            document.getElementById('modalEventoDescripcion').innerHTML = descripcionHtml;
                                            document.getElementById('modalEventoDetalle').showModal();
                                        }
                                    });
                                    this.calendarInstance.render();
                                    console.log("ModalDisponibilidad (rAF): Calendario renderizado.");

                                    // Renderizar Tabla (dentro de rAF también está bien)
                                    const detalles = data.tabla || [];
                                    if (detalles.length === 0) {
                                        tablaDetalleContenido.innerHTML = '<p class="text-gray-500 text-sm text-center mt-4">No hay cursos programados para este recurso.</p>';
                                    } else {
                                        let tablaHtml = `<div class="overflow-x-auto border rounded"><table class="w-full table-auto text-xs"><thead class="bg-gray-100 text-left"><tr><th class="px-2 py-1.5 border-b">Fecha</th><th class="px-2 py-1.5 border-b">Horario</th><th class="px-2 py-1.5 border-b">Curso</th><th class="px-2 py-1.5 border-b">Coordinación</th></tr></thead><tbody>`;
                                        detalles.forEach(ev => { tablaHtml += `<tr class="border-t"><td class="px-2 py-1.5">${ev.fecha}</td><td class="px-2 py-1.5">${ev.hora_inicio} - ${ev.hora_fin}</td><td class="px-2 py-1.5">${ev.curso}</td><td class="px-2 py-1.5"><span class="inline-block px-1.5 py-0.5 rounded text-white text-[10px]" style="background-color:${ev.color || '#9CA3AF'}">${ev.coordinacion}</span></td></tr>`; });
                                        tablaHtml += `</tbody></table></div>`;
                                        tablaDetalleContenido.innerHTML = tablaHtml;
                                    }
                                    console.log("ModalDisponibilidad (rAF): Tabla renderizada.");
                                    this.isLoadingAvailability = false; // Ocultar "Cargando"

                                    this.$nextTick(() => {
                                        console.log("ModalDisponibilidad: Forzando refresco de Alpine.");

                                } catch (renderError) {
                                    console.error("ModalDisponibilidad (rAF): ¡ERROR al renderizar!", renderError);
                                    if (calendarEl) calendarEl.innerHTML = `<p class="text-red-500 text-center p-4">Error al cargar calendario: ${renderError.message}</p>`;
                                    if (tablaDetalleContenido) tablaDetalleContenido.innerHTML = `<p class="text-red-500 text-center p-4">Error al mostrar detalles.</p>`;
                                    this.isLoadingAvailability = false; // Ocultar "Cargando" incluso en error
                                }
                            }); // <-- Fin de requestAnimationFrame
                        })
                        .catch(error => {
                            console.error(`ModalDisponibilidad: ERROR en fetch (${type}):`, error);
                            this.isLoadingAvailability = false;
                            if (calendarEl) calendarEl.innerHTML = `<p class="text-red-500 text-center p-4">Error API</p>`;
                            if (tablaDetalleContenido) tablaDetalleContenido.innerHTML = `<p class="text-red-500 text-sm text-center mt-4">Error al cargar los detalles: ${error.message}</p>`;
                        });
                }, // Fin openAvailabilityModal

                submitForm(event) { /* ...código existente... */ }

            } // Fin del objeto devuelto
        } // Fin de la función programacionForm
    </script>

    {{-- Asegúrate de que FullCalendar JS esté cargado ANTES de este script si no está global --}}
    {{-- @stack('scripts') --}}

</x-app-layout>
