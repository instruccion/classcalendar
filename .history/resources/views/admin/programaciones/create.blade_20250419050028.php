<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar Nuevo Curso
        </h2>
    </x-slot>

    {{-- 1. Cargar CSS de FullCalendar --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/main.min.css" rel="stylesheet">
    {{-- <link href="{{ asset('assets/css/minicalendar_custom.css') }}" rel="stylesheet"> --}}

    <div class="py-6 max-w-4xl mx-auto">
        {{-- Botón Volver (Opcional) --}}
        {{-- <a href="{{ route('admin.programaciones.index') }}" class="inline-flex items-center text-blue-600 hover:underline mb-4">
            <i class="mdi mdi-arrow-left mr-2 text-xl"></i> Volver al Listado
        </a> --}}

        {{-- 2. Contenedor principal con inicialización Alpine --}}
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

            {{-- ... (Resto del HTML del encabezado y formulario, igual que antes) ... --}}
             <div class="flex justify-between items-center mb-6 pb-2 border-b">
                <h1 class="text-2xl font-bold">Programar Curso</h1>
                <a href="{{ route('admin.programaciones.bloque.show') }}"
                   class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">
                    📦 Programar por Bloque
                </a>
            </div>

            <x-validation-errors class="mb-4" />

            <form :action="formAction" method="POST" id="form-programacion" @submit.prevent="submitForm"
                  class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                @csrf
                <input type="hidden" name="_method" x-bind:value="formMethod">

                {{-- Campos Grupo, Curso, Bloque, Fechas, Horas, Aula, Instructor --}}
                {{-- ... (Estos bloques de HTML no cambian) ... --}}

                 <!-- Grupo -->
                 <div>
                    <label for="grupo_id" class="block font-semibold mb-1">Grupo <span class="text-red-500">*</span></label>
                    <select name="grupo_id" id="grupo_id" required x-model="selectedGroupId" @change="loadCourses"
                            class="w-full border px-4 py-2 rounded bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Seleccione un grupo...</option>
                        <template x-for="grupo in grupos" :key="grupo.id">
                            {{-- Asegurarse que grupo.coordinacion existe antes de acceder a nombre --}}
                            <option :value="grupo.id" x-text="`${grupo.nombre} (${grupo.coordinacion ? grupo.coordinacion.nombre : 'Sin Coord.'})`"></option>
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
            </form> {{-- Fin del Formulario --}}
        </div> {{-- Fin del div x-data --}}

        {{-- Modales (igual que antes) --}}
        <dialog id="modalDisponibilidad" class="w-full max-w-4xl p-0 rounded-lg shadow-lg backdrop:bg-black/40">
             {{-- ... Contenido del modal de disponibilidad ... --}}
             <div class="bg-white p-6 relative">
                <button @click="$el.closest('dialog').close()" class="absolute top-2 right-3 text-gray-600 hover:text-gray-900 text-2xl leading-none">×</button>
                <h2 class="text-xl font-bold mb-4" id="modalTitulo">Disponibilidad</h2>
                <div x-show="isLoadingAvailability" class="text-center py-10">Cargando disponibilidad...</div>
                <div x-show="!isLoadingAvailability" x-transition class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div id="mini-calendar" class="border rounded p-2 h-auto max-h-[450px] overflow-auto text-sm"></div>
                    <div id="tabla-detalle" class="overflow-y-auto max-h-[450px]">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2 mt-1">📋 Detalle de ocupación</h3>
                        <div id="tabla-detalle-contenido">
                             <p class="text-gray-500 text-sm">Seleccione un recurso para ver detalles.</p>
                        </div>
                    </div>
                </div>
            </div>
        </dialog>

        <dialog id="modalEventoDetalle" class="w-[90%] max-w-md p-0 rounded-lg shadow-lg backdrop:bg-black/40">
            {{-- ... Contenido del modal de evento detalle ... --}}
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

        {{-- Toast (igual que antes) --}}
        <div id="toast-fecha-fin" x-show="showEndDateToast" x-cloak x-transition ...>
            📅 Fecha fin estimada: <span x-text="calculatedEndDateText"></span>
        </div>

    </div> {{-- Fin del contenedor py-6 --}}

    {{-- 3. Cargar JS de FullCalendar ANTES del script Alpine --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js'></script>
    {{-- Opcional: para idioma español --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/locales/es.js'></script>

    {{-- 4. Lógica Alpine.js con la modificación en openAvailabilityModal --}}
    <script>
        function programacionForm(config) {
            return {
                // ... (Estado y otros métodos igual que antes) ...
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

                init() { /* ... */ },
                get selectedCourseDuration() { /* ... */ },
                loadCourses() { /* ... */ },
                handleCourseChange() { /* ... */ },
                loadInstructors() { /* ... */ },
                calculateEndDateAndCheckAvailability() { /* ... */ },
                calculateEndDate() { /* ... */ },
                checkAvailability(type) { /* ... */ },

                // --- Modal de Disponibilidad con requestAnimationFrame ---
                openAvailabilityModal(type) {
                    const resourceId = type === 'instructor' ? this.selectedInstructorId : this.selectedAulaId;
                    if (!resourceId) return;

                    const resourceName = document.querySelector(`#${type}_id option:checked`)?.textContent || `Recurso ID ${resourceId}`;
                    document.getElementById('modalTitulo').innerHTML = `Disponibilidad de <span class="capitalize font-bold text-gray-900">${type}</span> – <span class="font-bold text-blue-700">${resourceName}</span>`;

                    const modal = document.getElementById('modalDisponibilidad');
                    const calendarEl = document.getElementById('mini-calendar');
                    const tablaDetalleContenido = document.getElementById('tabla-detalle-contenido');

                    if (!modal || typeof modal.showModal !== 'function') {
                        console.error("ModalDisponibilidad: Elemento <dialog> no encontrado o no soportado.");
                        alert("Error: No se puede abrir el modal de disponibilidad.");
                        return;
                    }

                    modal.showModal();
                    this.isLoadingAvailability = true; // Mostrar "Cargando..."
                    calendarEl.innerHTML = ''; // Limpiar contenido previo
                    tablaDetalleContenido.innerHTML = '<p class="text-gray-500 text-sm">Cargando detalles...</p>'; // Limpiar tabla

                    if (this.calendarInstance) {
                        this.calendarInstance.destroy();
                        this.calendarInstance = null;
                    }

                    const params = new URLSearchParams({ tipo: type, id: resourceId });
                    const url = `${this.rutasApi.detalleDisponibilidad}?${params.toString()}`;
                    console.log("ModalDisponibilidad: Llamando a API:", url); // Log M2

                    fetch(url)
                        .then(res => {
                            console.log("ModalDisponibilidad: Respuesta API recibida, status:", res.status); // Log M3
                            if (!res.ok) {
                                return res.json().then(errData => { throw new Error(errData.error || `Error HTTP ${res.status}`) });
                            }
                            return res.json();
                        })
                        .then(data => {
                            console.log("ModalDisponibilidad: Datos JSON recibidos:", data); // Log M4
                            // --- MOVER isLoadingAvailability DENTRO de requestAnimationFrame ---
                            // this.isLoadingAvailability = false; // <-- NO PONERLO AQUÍ

                            // --- Renderizar Calendario DENTRO de requestAnimationFrame ---
                            requestAnimationFrame(() => {
                                console.log("ModalDisponibilidad (rAF): Intentando renderizar calendario..."); // Log M5
                                try {
                                    if (typeof FullCalendar === 'undefined' || !FullCalendar.Calendar) {
                                        throw new Error('FullCalendar no está cargado.');
                                    }
                                    const currentCalendarEl = document.getElementById('mini-calendar');
                                    if (!currentCalendarEl) {
                                        throw new Error('Elemento #mini-calendar no encontrado en el DOM.');
                                    }
                                    console.log("ModalDisponibilidad (rAF): Elemento calendario encontrado, inicializando..."); // Log M5.5

                                    this.calendarInstance = new FullCalendar.Calendar(currentCalendarEl, {
                                        initialView: 'dayGridMonth',
                                        locale: 'es',
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
                                    console.log("ModalDisponibilidad (rAF): Calendario renderizado."); // Log M6

                                    // --- Renderizar Tabla DESPUÉS del calendario ---
                                    console.log("ModalDisponibilidad (rAF): Intentando renderizar tabla..."); // Log M7
                                     try {
                                         const detalles = data.tabla || [];
                                         if (detalles.length === 0) {
                                             tablaDetalleContenido.innerHTML = '<p class="text-gray-500 text-sm text-center mt-4">No hay cursos programados para este recurso.</p>';
                                         } else {
                                             let tablaHtml = `<div class="overflow-x-auto border rounded"><table class="w-full table-auto text-xs"><thead class="bg-gray-100 text-left"><tr><th class="px-2 py-1.5 border-b">Fecha</th><th class="px-2 py-1.5 border-b">Horario</th><th class="px-2 py-1.5 border-b">Curso</th><th class="px-2 py-1.5 border-b">Coordinación</th></tr></thead><tbody>`;
                                             detalles.forEach(ev => { tablaHtml += `<tr class="border-t"><td class="px-2 py-1.5">${ev.fecha}</td><td class="px-2 py-1.5">${ev.hora_inicio} - ${ev.hora_fin}</td><td class="px-2 py-1.5">${ev.curso}</td><td class="px-2 py-1.5"><span class="inline-block px-1.5 py-0.5 rounded text-white text-[10px]" style="background-color:${ev.color || '#9CA3AF'}">${ev.coordinacion}</span></td></tr>`; });
                                             tablaHtml += `</tbody></table></div>`;
                                             tablaDetalleContenido.innerHTML = tablaHtml;
                                         }
                                         console.log("ModalDisponibilidad (rAF): Tabla renderizada."); // Log M8
                                     } catch (tableError) {
                                         console.error("ModalDisponibilidad (rAF): ¡ERROR al renderizar tabla!", tableError);
                                         tablaDetalleContenido.innerHTML = `<p class="text-red-500 text-center p-4">Error al mostrar detalles: ${tableError.message}</p>`;
                                     }
                                     // --- Ocultar "Cargando..." AHORA que todo está listo ---
                                     this.isLoadingAvailability = false; // <-- MOVIDO AQUÍ

                                } catch (calendarError) {
                                    console.error("ModalDisponibilidad (rAF): ¡ERROR al renderizar FullCalendar!", calendarError);
                                    calendarEl.innerHTML = `<p class="text-red-500 text-center p-4">Error al cargar calendario: ${calendarError.message}</p>`;
                                     // Asegurarse de ocultar "Cargando" incluso si falla el calendario
                                     this.isLoadingAvailability = false; // <-- También aquí en caso de error
                                     // También renderizar la tabla si es posible, o mostrar error
                                     try { /* ... código para renderizar tabla ... */ } catch(e){}


                                }
                            }); // <-- Fin de requestAnimationFrame
                        })
                        .catch(error => {
                             console.error(`ModalDisponibilidad: ERROR en fetch o procesamiento (${type}):`, error); // Log M9
                             this.isLoadingAvailability = false; // Ocultar "Cargando" en error
                             calendarEl.innerHTML = `<p class="text-red-500 text-center p-4">Error API</p>`;
                             tablaDetalleContenido.innerHTML = `<p class="text-red-500 text-sm text-center mt-4">Error al cargar los detalles: ${error.message}</p>`;
                        });
                }, // Fin openAvailabilityModal

                submitForm(event) { /* ... */ }

            } // Fin del objeto devuelto
        } // Fin de la función programacionForm
    </script>

</x-app-layout>
