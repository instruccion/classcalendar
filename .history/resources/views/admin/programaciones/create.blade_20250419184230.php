<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar Nuevo Curso
        </h2>
    </x-slot>

    {{-- CSS de FullCalendar (Cargado vía Vite/app.css) --}}

    <div class="py-6 max-w-4xl mx-auto">

        {{-- ============================================= --}}
        {{--  SCRIPT ALPINE DEFINIDO ANTES DE USARSE      --}}
        {{-- ============================================= --}}
        <script>
            function programacionForm(config) {
                return {
                    // --- Estado ---
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
                    formAction: config.rutasApi.store, // Usa la ruta pasada desde el controlador
                    calendarInstance: null,
                    isModalOpen: false, // Para controlar modal disponibilidad (usando DIV)
                    isEventModalOpen: false, // Para controlar modal evento (usando DIV)

                    // --- Métodos ---
                    init() { console.log('Alpine: Formulario inicializado.'); },

                    get selectedCourseDuration() {
                        const cursoSelect = document.getElementById('curso_id');
                        const selectedOption = cursoSelect?.options[cursoSelect?.selectedIndex];
                        return parseInt(selectedOption?.dataset?.duracion || 0);
                    },

                    loadCourses() {
                        console.log('Alpine: loadCourses - Grupo ID:', this.selectedGroupId);
                        this.selectedCourseId = ''; this.availableCourses = [];
                        this.selectedInstructorId = ''; this.availableInstructors = [];
                        this.endDate = ''; this.endTime = '';
                        this.instructorOccupied = false; this.aulaOccupied = false;
                        if (!this.selectedGroupId) { console.log('Alpine: loadCourses - No hay grupo.'); return; }
                        this.isLoadingCourses = true;
                        const url = this.rutasApi.cursosPorGrupo.replace(':grupoId', this.selectedGroupId);
                        fetch(url)
                            .then(res => res.ok ? res.json() : Promise.reject('Error API Cursos'))
                            .then(data => { this.availableCourses = data; console.log('Alpine: loadCourses - Cursos OK:', data.length); })
                            .catch(error => { console.error("Alpine: Error loadCourses:", error); this.availableCourses = []; })
                            .finally(() => { this.isLoadingCourses = false; console.log('Alpine: loadCourses - Fin.'); });
                    },

                    handleCourseChange() {
                        console.log('Alpine: handleCourseChange - Curso ID:', this.selectedCourseId);
                        this.selectedInstructorId = ''; this.availableInstructors = [];
                        this.instructorOccupied = false;
                        this.loadInstructors();
                        this.calculateEndDateAndCheckAvailability();
                    },

                    loadInstructors() {
                        console.log('Alpine: loadInstructors - Curso ID:', this.selectedCourseId);
                        this.availableInstructors = [];
                        if (!this.selectedCourseId) { console.log('Alpine: loadInstructors - No hay curso.'); return; }
                        this.isLoadingInstructors = true;
                        const url = this.rutasApi.instructoresPorCurso.replace(':cursoId', this.selectedCourseId);
                        fetch(url)
                            .then(res => res.ok ? res.json() : res.json().then(err => { throw new Error(err.error || `Error HTTP ${res.status}`) }))
                            .then(data => { this.availableInstructors = data; console.log('Alpine: loadInstructors - Instructores OK:', data.length); })
                            .catch(error => { console.error("Alpine: Error loadInstructors:", error); this.availableInstructors = []; })
                            .finally(() => { this.isLoadingInstructors = false; console.log('Alpine: loadInstructors - Fin.'); });
                    },

                    calculateEndDateAndCheckAvailability() {
                        console.log('Alpine: calculateEndDateAndCheckAvailability');
                        this.calculateEndDate();
                    },

                    calculateEndDate() {
                        this.endDate = ''; this.endTime = '';
                        this.calculatedEndDateText = ''; this.showEndDateToast = false;
                        const duration = this.selectedCourseDuration;
                        console.log(`Alpine: calculateEndDate - Inicio=${this.startDate}, Duración=${duration}, HoraInicio=${this.startTime}`);
                        if (!this.startDate || !duration || duration <= 0) { console.log("Alpine: calculateEndDate - Faltan datos."); return; }
                        this.isLoadingEndDate = true;
                        fetch(this.rutasApi.calcularFechaFin, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                            body: JSON.stringify({ inicio: this.startDate, horas: duration, hora_inicio: this.startTime })
                        })
                        .then(res => res.ok ? res.json() : res.json().then(err => { throw new Error(err.error || `Error HTTP ${res.status}`) }))
                        .then(data => {
                            console.log("Alpine: calculateEndDate - Datos JSON:", data);
                            if (data.fecha_fin && data.hora_fin) {
                                this.endDate = data.fecha_fin; this.endTime = data.hora_fin;
                                this.calculatedEndDateText = `${data.fecha_fin} ${data.hora_fin}`; this.showEndDateToast = true;
                                console.log("Alpine: calculateEndDate - Fechas/Horas OK:", this.endDate, this.endTime);
                                setTimeout(() => this.showEndDateToast = false, 3500);
                            } else { console.error("Alpine: calculateEndDate - Respuesta inesperada:", data); alert("No se pudo calcular fecha/hora fin."); }
                        })
                        .catch(error => { console.error("Alpine: calculateEndDate ERROR fetch:", error); alert(`Error calculando fecha fin: ${error.message}`); this.endDate = ''; this.endTime = ''; })
                        .finally(() => {
                            this.isLoadingEndDate = false;
                            console.log("Alpine: calculateEndDate - Fin. Verificando disponibilidad...");
                            this.checkAvailability('instructor'); this.checkAvailability('aula');
                        });
                    },

                    checkAvailability(type) {
                        const resourceId = type === 'instructor' ? this.selectedInstructorId : this.selectedAulaId;
                        const startDate = this.startDate; const endDate = this.endDate;
                        const startTime = this.startTime; const endTime = this.endTime;
                        console.log(`Alpine: CheckAvailability (${type}) - R:${resourceId}, S:${startDate} ${startTime}, E:${endDate} ${endTime}`);
                        if(type === 'instructor') this.instructorOccupied = false; else this.aulaOccupied = false;
                        if (!resourceId || !startDate || !endDate || !startTime || !endTime) { console.log(`Alpine: CheckAvailability (${type}) - Faltan datos.`); return; }
                        const params = new URLSearchParams({ tipo: type, id: resourceId, fecha_inicio: startDate, fecha_fin: endDate, hora_inicio: startTime, hora_fin: endTime });
                        const url = `${this.rutasApi.verificarDisponibilidad}?${params.toString()}`;
                        fetch(url)
                            .then(res => res.ok ? res.json() : Promise.reject(`Error API Verificar ${type}`))
                            .then(data => {
                                console.log(`Alpine: CheckAvailability (${type}) - Ocupado =`, data.ocupado);
                                if (type === 'instructor') this.instructorOccupied = data.ocupado; else this.aulaOccupied = data.ocupado;
                            })
                            .catch(error => { console.error(`Alpine: Error checkAvailability (${type}):`, error); });
                    },

                    // --- Modal de Disponibilidad (Simplificado, usando DIV y estado Alpine) ---
                    openAvailabilityModal(type) {
                        const resourceId = type === 'instructor' ? this.selectedInstructorId : this.selectedAulaId;
                        if (!resourceId) { alert('Seleccione un ' + type + ' primero.'); return; } // Aviso si no hay recurso

                        const resourceName = document.querySelector(`#${type}_id option:checked`)?.textContent || `Recurso ID ${resourceId}`;
                        const modalDiv = document.getElementById('modalDisponibilidad'); // El DIV modal
                        const calendarEl = document.getElementById('mini-calendar');
                        const tablaDetalleContenido = document.getElementById('tabla-detalle-contenido');
                        const modalTituloEl = document.getElementById('modalTitulo');

                        if (!modalDiv || !calendarEl || !tablaDetalleContenido || !modalTituloEl) {
                            console.error("Alpine: Elementos del modal no encontrados."); alert("Error al preparar modal."); return;
                        }

                        modalTituloEl.innerHTML = `Disponibilidad de <span class="capitalize font-bold text-gray-900">${type}</span> – <span class="font-bold text-blue-700">${resourceName}</span>`;
                        this.isModalOpen = true; // Mostrar el modal DIV
                        this.isLoadingAvailability = true; // Mostrar "Cargando..."
                        calendarEl.innerHTML = ''; tablaDetalleContenido.innerHTML = '<p class="text-gray-500 text-sm">Cargando detalles...</p>';
                        if (this.calendarInstance) { this.calendarInstance.destroy(); this.calendarInstance = null; }

                        const url = `${this.rutasApi.detalleDisponibilidad}?${new URLSearchParams({ tipo: type, id: resourceId }).toString()}`;
                        console.log("Alpine: openAvailabilityModal - Fetching:", url);

                        fetch(url)
                            .then(res => res.ok ? res.json() : res.json().then(err => { throw new Error(err.error || `Error HTTP ${res.status}`) }))
                            .then(data => {
                                console.log("Alpine: openAvailabilityModal - Datos JSON:", data);
                                // Renderizar directamente, sin rAF por ahora para simplificar
                                try {
                                    if (typeof window.FullCalendar === 'undefined' || !window.FullCalendar) throw new Error('FullCalendar no está en window.');
                                    console.log("Alpine: openAvailabilityModal - Inicializando FullCalendar...");
                                    this.calendarInstance = new window.FullCalendar(calendarEl, {
                                        plugins: [ window.dayGridPlugin, window.interactionPlugin ], initialView: 'dayGridMonth', locale: window.esLocale, height: 'auto',
                                        events: data.eventos || [], headerToolbar: { left: 'prev,next', center: 'title', right: '' }, displayEventTime: false,
                                        eventClick: (info) => {
                                            const props = info.event.extendedProps; const self = this;
                                            document.getElementById('modalEventoTitulo').textContent = info.event.title; document.getElementById('modalEventoColor').style.backgroundColor = props.color || '#9CA3AF';
                                            document.getElementById('modalEventoDescripcion').innerHTML = `<p><strong>Grupo:</strong> ${props.grupo || 'N/A'}</p><p><strong>Fechas:</strong> ${props.fecha_inicio_fmt || '?'} - ${props.fecha_fin_fmt || '?'}</p><p><strong>Horario:</strong> ${props.hora_inicio_fmt || '?'} - ${props.hora_fin_fmt || '?'}</p><p><strong>${props.tipo_recurso_opuesto || 'Recurso'}:</strong> ${props.nombre_recurso_opuesto || 'N/A'}</p><p><strong>Coordinación:</strong> ${props.coordinacion || 'N/A'}</p>`;
                                            self.isEventModalOpen = true; // Mostrar modal de evento
                                        }
                                    });
                                    this.calendarInstance.render();
                                    console.log("Alpine: openAvailabilityModal - Calendario OK.");

                                    console.log("Alpine: openAvailabilityModal - Renderizando tabla...");
                                    const detalles = data.tabla || [];
                                    if (detalles.length === 0) { tablaDetalleContenido.innerHTML = '<p class="text-gray-500 text-sm text-center mt-4">No hay cursos programados.</p>'; }
                                    else { let tablaHtml = `<div class="overflow-x-auto border rounded"><table class="w-full table-auto text-xs"><thead class="bg-gray-100 text-left"><tr><th class="px-2 py-1.5 border-b">Fecha</th><th class="px-2 py-1.5 border-b">Horario</th><th class="px-2 py-1.5 border-b">Curso</th><th class="px-2 py-1.5 border-b">Coordinación</th></tr></thead><tbody>`; detalles.forEach(ev => { tablaHtml += `<tr class="border-t"><td class="px-2 py-1.5">${ev.fecha}</td><td class="px-2 py-1.5">${ev.hora_inicio} - ${ev.hora_fin}</td><td class="px-2 py-1.5">${ev.curso}</td><td class="px-2 py-1.5"><span class="inline-block px-1.5 py-0.5 rounded text-white text-[10px]" style="background-color:${ev.color || '#9CA3AF'}">${ev.coordinacion}</span></td></tr>`; }); tablaHtml += `</tbody></table></div>`; tablaDetalleContenido.innerHTML = tablaHtml; }
                                    console.log("Alpine: openAvailabilityModal - Tabla OK.");
                                } catch (renderError) {
                                     console.error("Alpine: openAvailabilityModal ¡ERROR RENDER!", renderError);
                                     if (calendarEl) calendarEl.innerHTML = `<p>Error calendario: ${renderError.message}</p>`;
                                     if (tablaDetalleContenido) tablaDetalleContenido.innerHTML = `<p>Error tabla.</p>`;
                                } finally {
                                     this.isLoadingAvailability = false; // Ocultar "Cargando"
                                     console.log("Alpine: openAvailabilityModal - isLoadingAvailability puesto a false (finally).");
                                }
                            })
                            .catch(error => {
                                 console.error(`Alpine: openAvailabilityModal ERROR fetch (${type}):`, error);
                                 this.isLoadingAvailability = false;
                                 const currentCalendarEl = document.getElementById('mini-calendar'); const currentTablaDetalleContenido = document.getElementById('tabla-detalle-contenido');
                                 if (currentCalendarEl) currentCalendarEl.innerHTML = `<p>Error API</p>`; if (currentTablaDetalleContenido) currentTablaDetalleContenido.innerHTML = `<p>Error al cargar: ${error.message}</p>`;
                            });
                    }, // Fin openAvailabilityModal

                    submitForm(event) {
                         console.log('Alpine: Validando...'); if (!this.selectedGroupId) { alert('Seleccione Grupo.'); return; } if (!this.selectedCourseId) { alert('Seleccione Curso.'); return; } if (!this.startDate) { alert('Seleccione Fecha Inicio.'); return; } if (!this.endDate) { alert('Falta Fecha Fin.'); return; } if (!this.selectedAulaId) { alert('Seleccione Aula.'); return; } if (!this.selectedInstructorId) { alert('Seleccione Instructor.'); return; } if (this.aulaOccupied) { if (!confirm('ADVERTENCIA: Aula ocupada. ¿Continuar?')) return; } if (this.instructorOccupied) { if (!confirm('ADVERTENCIA: Instructor ocupado. ¿Continuar?')) return; } console.log('Alpine: Enviando formulario...'); event.target.submit();
                     }

                } // Fin del return
            } // Fin de programacionForm
        </script>
        {{-- ============================================= --}}
        {{-- FIN SCRIPT ALPINE --}}
        {{-- ============================================= --}}


        {{-- DIV QUE USA EL SCRIPT ALPINE (x-data) --}}
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
                     detalleDisponibilidad: '{{ route('admin.api.programaciones.detalleDisponibilidad') }}',
                     store: '{{ route('admin.programaciones.store') }}'
                 },
                 csrfToken: '{{ csrf_token() }}'
             })"
             x-init="init()">

            {{-- Contenido del formulario (HTML) --}}
            <div class="flex justify-between items-center mb-6 pb-2 border-b">
                <h1 class="text-2xl font-bold">Programar Curso</h1>
                <a href="{{ route('admin.programaciones.bloque.show') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">
                    📦 Programar por Bloque
                </a>
            </div>

            <x-validation-errors class="mb-4" />

            <form x-bind:action="formAction" method="POST" id="form-programacion" @submit.prevent="submitForm"
                  class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                @csrf
                <input type="hidden" name="_method" x-bind:value="formMethod">

                 <!-- Campos del formulario -->
                 <!-- Grupo -->
                 <div>
                    <label for="grupo_id" class="block font-semibold mb-1">Grupo <span class="text-red-500">*</span></label>
                    <select name="grupo_id" id="grupo_id" required x-model="selectedGroupId" @change="loadCourses"
                            class="w-full border px-4 py-2 rounded bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Seleccione un grupo...</option>
                        <template x-for="grupo in grupos" :key="grupo.id">
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
                </div>
                 <!-- Bloque -->
                 <div class="md:col-span-2 flex items-center gap-4 border-t pt-4 mt-2">
                     <label for="usa_bloque" class="font-semibold flex items-center gap-2 cursor-pointer">
                         <input type="checkbox" id="usa_bloque" x-model="useBlockCode" class="form-checkbox text-blue-600 h-5 w-5 rounded focus:ring-blue-500">
                         ¿Pertenece a un bloque?
                     </label>
                     <input type="text" name="bloque_codigo" id="bloque_codigo" x-model="blockCode" :disabled="!useBlockCode" class="border px-4 py-2 rounded w-full md:w-1/3 disabled:bg-gray-100 disabled:cursor-not-allowed" placeholder="Código de bloque (opcional)">
                 </div>
                 <!-- Fechas y Horas -->
                 <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-4 border-t pt-4 mt-2">
                     <div>
                         <label for="fecha_inicio" class="block font-semibold mb-1">Fecha Inicio <span class="text-red-500">*</span></label>
                         <input type="date" name="fecha_inicio" id="fecha_inicio" required x-model="startDate" @change="calculateEndDateAndCheckAvailability" class="w-full border px-4 py-2 rounded focus:border-indigo-500 focus:ring-indigo-500">
                     </div>
                     <div>
                         <label for="hora_inicio" class="block font-semibold mb-1">Hora Inicio <span class="text-red-500">*</span></label>
                         <input type="time" name="hora_inicio" id="hora_inicio" required x-model="startTime" @change="checkAvailability('instructor'); checkAvailability('aula')" class="w-full border px-4 py-2 rounded focus:border-indigo-500 focus:ring-indigo-500" value="08:30">
                     </div>
                     <div>
                         <label for="fecha_fin" class="block font-semibold mb-1">Fecha Fin (Estimada)</label>
                         <input type="date" name="fecha_fin" id="fecha_fin" x-model="endDate" readonly class="w-full border px-4 py-2 rounded bg-gray-100 cursor-not-allowed">
                     </div>
                     <div>
                         <label for="hora_fin" class="block font-semibold mb-1">Hora Fin (Estimada)</label>
                         <input type="time" name="hora_fin" id="hora_fin" x-model="endTime" readonly class="w-full border px-4 py-2 rounded bg-gray-100 cursor-not-allowed">
                     </div>
                 </div>
                 <!-- Aula -->
                 <div class="border-t pt-4 mt-2">
                     <label for="aula_id" class="block font-semibold mb-1">Aula <span class="text-red-500">*</span></label>
                     <div class="flex gap-2 items-center">
                         <select name="aula_id" id="aula_id" required x-model="selectedAulaId" @change="checkAvailability('aula')" class="w-full border px-4 py-2 rounded bg-white focus:border-indigo-500 focus:ring-indigo-500" :class="{ 'border-red-500 text-red-600 font-bold': aulaOccupied }">
                             <option value="">Seleccione un aula...</option>
                             <template x-for="aula in aulasActivas" :key="aula.id">
                                 <option :value="aula.id" x-text="aula.nombre + (aula.lugar ? ` – ${aula.lugar}` : '')"></option>
                             </template>
                         </select>
                         <button type="button" @click="openAvailabilityModal('aula')" :disabled="!selectedAulaId" class="text-blue-600 hover:text-blue-800 text-xl p-1 disabled:text-gray-400 disabled:cursor-not-allowed" title="Ver disponibilidad del aula">
                             <i class="mdi mdi-calendar-search"></i>
                         </button>
                     </div>
                     <div id="msg-aula" class="text-sm mt-1 text-red-600" x-show="aulaOccupied" x-cloak> ⚠️ Esta aula ya está ocupada en las fechas/horas seleccionadas. </div>
                 </div>
                 <!-- Instructor -->
                 <div class="border-t pt-4 mt-2">
                     <label for="instructor_id" class="block font-semibold mb-1">Instructor <span class="text-red-500">*</span></label>
                     <div class="flex gap-2 items-center">
                         <select name="instructor_id" id="instructor_id" required x-model="selectedInstructorId" @change="checkAvailability('instructor')" :disabled="isLoadingInstructors || !selectedCourseId" class="w-full border px-4 py-2 rounded bg-white focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100" :class="{ 'border-red-500 text-red-600 font-bold': instructorOccupied }">
                             <option value="" x-show="!selectedCourseId">Seleccione un curso primero...</option>
                             <option value="" x-show="selectedCourseId && isLoadingInstructors">Cargando instructores...</option>
                             <option value="" x-show="selectedCourseId && !isLoadingInstructors && availableInstructors.length === 0">No hay instructores para este curso...</option>
                             <template x-for="instructor in availableInstructors" :key="instructor.id">
                                 <option :value="instructor.id" x-text="instructor.nombre"></option>
                             </template>
                         </select>
                         <button type="button" @click="openAvailabilityModal('instructor')" :disabled="!selectedInstructorId" class="text-blue-600 hover:text-blue-800 text-xl p-1 disabled:text-gray-400 disabled:cursor-not-allowed" title="Ver disponibilidad del instructor">
                             <i class="mdi mdi-calendar-search"></i>
                         </button>
                     </div>
                     <div id="msg-instructor" class="text-sm mt-1 text-red-600" x-show="instructorOccupied" x-cloak> ⚠️ Este instructor ya está ocupado en las fechas/horas seleccionadas. </div>
                 </div>
                 <!-- Botón de Envío -->
                 <div class="md:col-span-2 text-center mt-6 pt-4 border-t">
                     <button type="submit" :disabled="isLoadingEndDate || isLoadingCourses || isLoadingInstructors" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-wait">
                         <span x-show="!isLoadingEndDate">Confirmar Programación</span>
                         <span x-show="isLoadingEndDate">Calculando fecha fin...</span>
                     </button>
                 </div>

            </form> {{-- Fin del Formulario --}}

        </div> {{-- Fin del div x-data --}}

        {{-- Modales (Usando DIVs y controlados por Alpine) --}}
        <div id="modalDisponibilidad"
             x-show="isModalOpen"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @keydown.escape.window="isModalOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
             style="display: none;" x-cloak>

             <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-4xl relative" @click.outside="isModalOpen = false">
                {{-- Botón de cierre usa Alpine --}}
                <button @click="isModalOpen = false" class="absolute top-2 right-3 text-gray-600 hover:text-gray-900 text-2xl leading-none">×</button>
                <h2 class="text-xl font-bold mb-4" id="modalTitulo">Disponibilidad</h2>
                {{-- Mensaje de Carga con x-show --}}
                <div x-show="isLoadingAvailability" class="text-center py-10">Cargando disponibilidad...</div>
                {{-- Contenido con x-show --}}
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
        </div>

        <div id="modalEventoDetalle"
             x-show="isEventModalOpen"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
             @keydown.escape.window="isEventModalOpen = false"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
             style="display: none;" x-cloak>

            <div class="bg-white p-6 rounded-lg shadow-xl w-[90%] max-w-md text-center relative" @click.outside="isEventModalOpen = false">
                 <h2 id="modalEventoTitulo" class="text-xl font-bold text-gray-800 mb-2">Detalle del Evento</h2>
                 <div class="flex items-center justify-center gap-3 mb-4">
                    <span id="modalEventoColor" class="w-4 h-4 rounded-full inline-block border border-gray-300 flex-shrink-0"></span>
                    <p id="modalEventoDescripcion" class="text-gray-700 text-sm text-left"></p>
                </div>
                 {{-- Botón de cierre usa Alpine --}}
                 <button @click="isEventModalOpen = false" class="mt-4 bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400 transition">
                     Cerrar
                 </button>
             </div>
        </div>

        {{-- Toast --}}
        <div id="toast-fecha-fin" x-show="showEndDateToast" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed bottom-5 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50">
            📅 Fecha fin estimada: <span x-text="calculatedEndDateText"></span>
        </div>

    </div> {{-- Fin del div py-6 --}}

</x-app-layout>
