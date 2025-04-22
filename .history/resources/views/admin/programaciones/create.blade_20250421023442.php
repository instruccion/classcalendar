<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar Nuevo Curso
        </h2>
    </x-slot>

    {{-- CSS de FullCalendar se carga desde app.css (vía Vite/NPM) --}}
    {{-- No se necesitan links CDN aquí --}}

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

                    // --- Modal de Disponibilidad (Versión Simplificada Final) ---
                    openAvailabilityModal(type) {
                        const resourceId = type === 'instructor' ? this.selectedInstructorId : this.selectedAulaId;
                        if (!resourceId) return;

                        const resourceName = document.querySelector(`#${type}_id option:checked`)?.textContent || `Recurso ID ${resourceId}`;
                        const modal = document.getElementById('modalDisponibilidad');
                        const calendarEl = document.getElementById('mini-calendar');
                        const tablaDetalleContenido = document.getElementById('tabla-detalle-contenido');

                        if (!modal || !calendarEl || !tablaDetalleContenido || typeof modal.showModal !== 'function') {
                            console.error("Alpine: Modal o elementos internos no encontrados."); return;
                        }

                        document.getElementById('modalTitulo').innerHTML = `Disponibilidad de <span class="capitalize font-bold text-gray-900">${type}</span> – <span class="font-bold text-blue-700">${resourceName}</span>`;
                        modal.showModal();
                        this.isLoadingAvailability = true;
                        calendarEl.innerHTML = ''; tablaDetalleContenido.innerHTML = '<p class="text-gray-500 text-sm">Cargando detalles...</p>';
                        if (this.calendarInstance) { this.calendarInstance.destroy(); this.calendarInstance = null; }

                        const url = `${this.rutasApi.detalleDisponibilidad}?${new URLSearchParams({ tipo: type, id: resourceId }).toString()}`;
                        console.log("Alpine: openAvailabilityModal - Fetching:", url);

                        fetch(url)
                            .then(res => res.ok ? res.json() : res.json().then(err => { throw new Error(err.error || `Error HTTP ${res.status}`) }))
                            .then(data => {
                                console.log("Alpine: openAvailabilityModal - Datos JSON:", data);
                                // Usar requestAnimationFrame para asegurar que el DOM esté listo para FullCalendar
                                requestAnimationFrame(() => {
                                    try {
                                        if (typeof window.FullCalendar === 'undefined' || !window.FullCalendar) throw new Error('FullCalendar no está en window.');

                                        console.log("Alpine: openAvailabilityModal (rAF) - Inicializando FullCalendar...");
                                        this.calendarInstance = new window.FullCalendar(calendarEl, {
                                             plugins: [ window.dayGridPlugin, window.interactionPlugin ],
                                             initialView: 'dayGridMonth', locale: window.esLocale, height: 'auto',
                                             events: data.eventos || [], headerToolbar: { left: 'prev,next', center: 'title', right: '' },
                                             displayEventTime: false,
                                             eventClick: (info) => {
                                                const props = info.event.extendedProps;
                                                const color = props.color || '#9CA3AF';

                                                document.getElementById('modalEventoTitulo').textContent = info.event.title;

                                                const colorCircle = document.getElementById('modalEventoColor');
                                                colorCircle.style.backgroundColor = color;
                                                colorCircle.style.borderColor = color; // ← Esta línea es clave

                                                document.getElementById('modalEventoDescripcion').innerHTML = `
                                                    <p><strong>Grupo:</strong> ${props.grupo || 'N/A'}</p>
                                                    <p><strong>Fechas:</strong> ${props.fecha_inicio_fmt || '?' } - ${props.fecha_fin_fmt || '?'}</p>
                                                    <p><strong>Horario:</strong> ${props.hora_inicio_fmt || '?' } - ${props.hora_fin_fmt || '?'}</p>
                                                    <p><strong>${props.tipo_recurso_opuesto || 'Recurso'}:</strong> ${props.nombre_recurso_opuesto || 'N/A'}</p>
                                                    <p><strong>Coordinación:</strong> ${props.coordinacion || 'N/A'}</p>
                                                `;

                                                document.getElementById('modalEventoDetalle').showModal();
                                            }

                                         });
                                        this.calendarInstance.render();
                                        console.log("Alpine: openAvailabilityModal (rAF) - Calendario OK.");

                                        console.log("Alpine: openAvailabilityModal (rAF) - Renderizando tabla...");
                                        const detalles = data.tabla || [];
                                        if (detalles.length === 0) { tablaDetalleContenido.innerHTML = '<p class="text-gray-500 text-sm text-center mt-4">No hay cursos programados.</p>'; }
                                        else {
                                             let tablaHtml = `<div class="overflow-x-auto border rounded"><table class="w-full table-auto text-xs"><thead class="bg-gray-100 text-left"><tr><th class="px-2 py-1.5 border-b">Fecha</th><th class="px-2 py-1.5 border-b">Horario</th><th class="px-2 py-1.5 border-b">Curso</th><th class="px-2 py-1.5 border-b">Coordinación</th></tr></thead><tbody>`;
                                             detalles.forEach(ev => { tablaHtml += `<tr class="border-t"><td class="px-2 py-1.5">${ev.fecha}</td><td class="px-2 py-1.5">${ev.hora_inicio} - ${ev.hora_fin}</td><td class="px-2 py-1.5">${ev.curso}</td><td class="px-2 py-1.5"><span class="inline-block px-1.5 py-0.5 rounded text-white text-[10px]" style="background-color:${ev.color || '#9CA3AF'}">${ev.coordinacion}</span></td></tr>`; });
                                             tablaHtml += `</tbody></table></div>`;
                                             tablaDetalleContenido.innerHTML = tablaHtml;
                                         }
                                        console.log("Alpine: openAvailabilityModal (rAF) - Tabla OK.");

                                    } catch (renderError) {
                                         console.error("Alpine: openAvailabilityModal (rAF) ¡ERROR RENDER!", renderError);
                                         if (calendarEl) calendarEl.innerHTML = `<p class="text-red-500 p-4">Error calendario: ${renderError.message}</p>`;
                                         if (tablaDetalleContenido) tablaDetalleContenido.innerHTML = `<p class="text-red-500 p-4">Error tabla.</p>`;
                                    } finally {
                                         // Ocultar "Cargando..." DESPUÉS de intentar renderizar
                                         this.isLoadingAvailability = false;
                                         console.log("Alpine: openAvailabilityModal (rAF) - isLoadingAvailability puesto a false (finally).");
                                    }
                                }); // Fin rAF
                            })
                            .catch(error => {
                                 console.error(`Alpine: openAvailabilityModal ERROR fetch (${type}):`, error);
                                 this.isLoadingAvailability = false; // Ocultar en error fetch
                                 const currentCalendarEl = document.getElementById('mini-calendar');
                                 const currentTablaDetalleContenido = document.getElementById('tabla-detalle-contenido');
                                 if (currentCalendarEl) currentCalendarEl.innerHTML = `<p class="text-red-500 p-4">Error API</p>`;
                                 if (currentTablaDetalleContenido) currentTablaDetalleContenido.innerHTML = `<p class="text-red-500 p-4">Error al cargar: ${error.message}</p>`;
                            });
                    }, // Fin openAvailabilityModal

                    submitForm(event) {
                        console.log('Alpine: Validando antes de enviar...');

                        let toastMessage = '';

                        if (!this.selectedGroupId) {
                            alert('Seleccione Grupo.');
                            return;
                        }

                        if (!this.selectedCourseId) {
                            alert('Seleccione Curso.');
                            return;
                        }

                        if (!this.startDate) {
                            alert('Seleccione Fecha Inicio.');
                            return;
                        }

                        if (!this.endDate) {
                            alert('Falta Fecha Fin.');
                            return;
                        }

                        if (!this.selectedAulaId) {
                            toastMessage += '⚠️ No se seleccionó aula.\n';
                        }

                        if (!this.selectedInstructorId) {
                            toastMessage += '⚠️ No se seleccionó instructor.\n';
                        }

                        if (this.aulaOccupied) {
                            if (!confirm('⚠️ Aula ocupada. ¿Deseas continuar?')) return;
                        }

                        if (this.instructorOccupied) {
                            if (!confirm('⚠️ Instructor ocupado. ¿Deseas continuar?')) return;
                        }

                        if (toastMessage !== '') {
                            this.showToast(toastMessage, 'warning');
                        }

                        console.log('Alpine: Enviando formulario...');
                        event.target.submit();
                    }

                    showToast(message, type = 'info') {
                        const toast = document.createElement('div');
                        toast.className = `fixed top-5 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow-lg z-50 text-sm transition-opacity duration-300 ${
                            type === 'warning' ? 'bg-yellow-500 text-black' :
                            type === 'success' ? 'bg-green-600 text-white' :
                            type === 'error' ? 'bg-red-600 text-white' : 'bg-blue-500 text-white'
                        }`;
                        toast.textContent = message;
                        document.body.appendChild(toast);
                        setTimeout(() => toast.classList.add('opacity-0'), 3000);
                        setTimeout(() => toast.remove(), 3500);
                    }


                } // Fin del return
            } // Fin de programacionForm
        </script>
        {{-- ============================================= --}}
        {{-- FIN SCRIPT ALPINE --}}
        {{-- ============================================= --}}


        {{-- DIV QUE USA EL SCRIPT ALPINE (x-data) --}}
        <div class="bg-white p-6 rounded shadow-md"
            x-data='programacionForm({
                grupos: @json($grupos),
                instructoresActivos: @json($instructores),
                aulasActivas: @json($aulas),
                feriados: @json($feriados),
                rutasApi: {
                    cursosPorGrupo: "{{ route('admin.api.programaciones.cursosPorGrupo', ['grupo' => ':grupoId']) }}",
                    instructoresPorCurso: "{{ route('admin.api.programaciones.instructoresPorCurso', ['curso' => ':cursoId']) }}",
                    calcularFechaFin: "{{ route('admin.api.programaciones.calcularFechaFin') }}",
                    verificarDisponibilidad: "{{ route('admin.api.programaciones.verificarDisponibilidad') }}",
                    detalleDisponibilidad: "{{ route('admin.api.programaciones.detalleDisponibilidad') }}",
                    store: "{{ route('admin.programaciones.store') }}"
                },
                csrfToken: "{{ csrf_token() }}"
            })'
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

                {{-- Campos del formulario --}}
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
                         <select name="instructor_id" id="instructor_id" x-model="selectedInstructorId" @change="checkAvailability('instructor')" :disabled="isLoadingInstructors || !selectedCourseId" class="w-full border px-4 py-2 rounded bg-white focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100" :class="{ 'border-red-500 text-red-600 font-bold': instructorOccupied }">
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

            {{-- Toast de Fecha Fin Estimada --}}
            <div x-show="showEndDateToast"
                x-transition
                x-cloak
                class="fixed bottom-5 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50"
                x-init="$watch('showEndDateToast', val => { if (val) setTimeout(() => showEndDateToast = false, 3500) })">
                📅 Fecha fin estimada: <span x-text="calculatedEndDateText"></span>
            </div>


        </div> {{-- Fin del div x-data --}}

        {{-- Modales y Toast --}}
        <dialog id="modalDisponibilidad" class="w-full max-w-4xl p-0 rounded-lg shadow-lg backdrop:bg-black/40"
        x-data="{ isLoadingAvailability: false }"
        x-on:open-modal-availability.window="isLoadingAvailability = true"
        x-on:close-modal-availability.window="isLoadingAvailability = false">
            <div class="bg-white p-6 relative">

                 {{-- Usamos el cierre directo por ID --}}
                 <button onclick="document.getElementById('modalDisponibilidad').close()" class="absolute top-2 right-3 text-gray-600 hover:text-gray-900 text-2xl leading-none">×</button>
                 <h2 class="text-xl font-bold mb-4" id="modalTitulo">Disponibilidad</h2>

                 {{-- Mensaje de Carga con x-show --}}
                 <div x-show="isLoadingAvailability" x-transition class="text-center py-10">Cargando disponibilidad...</div>

                 {{-- Contenido con x-show (siempre en el DOM, solo se oculta/muestra) --}}
                 <div x-show="!isLoadingAvailability" x-transition x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
             {{-- Contenido del modal --}}
             <div class="bg-white p-6 text-center relative">
                 <h2 id="modalEventoTitulo" class="text-xl font-bold text-gray-800 mb-2">Detalle del Evento</h2>
                 <div class="flex items-center justify-center gap-3 mb-4">
                     <span id="modalEventoColor" class="w-4 h-4 rounded-full inline-block border border-gray-300 flex-shrink-0"></span>
                     <p id="modalEventoDescripcion" class="text-gray-700 text-sm text-left"></p>
                 </div>
                 {{-- Usaremos $dispatch para cerrar este modal también, por consistencia --}}
                 <button onclick="document.getElementById('modalEventoDetalle').close()" class="mt-4 bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400 transition">
                     Cerrar
                 </button>
             </div>
        </dialog>

    </div> {{-- Fin del div py-6 --}}


</x-app-layout>
