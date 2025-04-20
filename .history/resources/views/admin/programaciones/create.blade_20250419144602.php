<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar Nuevo Curso
        </h2>
    </x-slot>

    {{-- CSS de FullCalendar (Asegúrate que se cargue, ya sea aquí o vía Vite en app.css) --}}
    {{-- Si usas Vite, esta línea debe estar comentada o eliminada --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/main.min.css" rel="stylesheet"> --}}

    <div class="py-6 max-w-4xl mx-auto">

        {{-- ===================================================================== --}}
        {{--  INICIO: Bloque de Script Alpine.js (Definición de la función)       --}}
        {{--  Este bloque DEBE ir ANTES del div con x-data que lo utiliza.       --}}
        {{-- ===================================================================== --}}
        <script>
            function programacionForm(config) {
                return {
                    // --- Estado del Componente Alpine ---
                    grupos: config.grupos || [],
                    instructoresActivos: config.instructoresActivos || [], // Estos son todos los activos, se filtrarán luego
                    aulasActivas: config.aulasActivas || [],
                    feriados: config.feriados || [], // Array de fechas 'YYYY-MM-DD'
                    rutasApi: config.rutasApi,
                    csrfToken: config.csrfToken,

                    // Datos del formulario
                    selectedGroupId: '',
                    selectedCourseId: '',
                    selectedInstructorId: '',
                    selectedAulaId: '',
                    startDate: '',
                    startTime: '08:30', // Valor inicial por defecto
                    endDate: '',        // Calculada o manual
                    endTime: '',        // Calculada o manual
                    useBlockCode: false,
                    blockCode: '',

                    // Datos para selects dinámicos
                    availableCourses: [],
                    availableInstructors: [], // Instructores filtrados por curso

                    // Indicadores de carga
                    isLoadingCourses: false,
                    isLoadingInstructors: false,
                    isLoadingEndDate: false,
                    isLoadingAvailability: false,

                    // Indicadores de disponibilidad
                    instructorOccupied: false,
                    aulaOccupied: false,

                    // Para el Toast de fecha fin
                    showEndDateToast: false,
                    calculatedEndDateText: '',

                    // Para manejar edición vs creación (si se usa el mismo form)
                    isEditing: false, // Añadido para futura edición
                    programacionId: null, // Añadido para futura edición
                    formMethod: 'POST', // Por defecto para crear
                    formAction: config.rutasApi.store, // Usar ruta desde config

                    // Instancia del calendario del modal
                    calendarInstance: null,

                    // --- Métodos del Componente Alpine ---
                    init() {
                        console.log('Formulario inicializado con config:', config);
                        // Si necesitas inicializar algo basado en datos de edición, iría aquí
                        // Ejemplo: this.isEditing = config.isEditing || false;
                        //          if(this.isEditing) { /* ... llenar campos ... */ }
                    },

                    // Propiedad computada para obtener la duración del curso seleccionado
                    get selectedCourseDuration() {
                        const cursoSelect = document.getElementById('curso_id');
                        // Usamos optional chaining (?) por si no hay opción seleccionada
                        const selectedOption = cursoSelect?.options[cursoSelect?.selectedIndex];
                        // Devolvemos 0 si no hay duración o no hay opción
                        return parseInt(selectedOption?.dataset?.duracion || 0);
                    },

                    // Carga los cursos cuando se selecciona un grupo
                    loadCourses() {
                        console.log('loadCourses - Grupo seleccionado:', this.selectedGroupId);
                        // Resetear todo lo dependiente del grupo/curso
                        this.selectedCourseId = '';
                        this.availableCourses = [];
                        this.selectedInstructorId = '';
                        this.availableInstructors = [];
                        this.endDate = '';
                        this.endTime = '';
                        this.instructorOccupied = false;
                        this.aulaOccupied = false;

                        if (!this.selectedGroupId) return; // Salir si no hay grupo

                        this.isLoadingCourses = true;
                        const url = this.rutasApi.cursosPorGrupo.replace(':grupoId', this.selectedGroupId);
                        console.log('loadCourses - Fetching:', url);

                        fetch(url)
                            .then(res => {
                                if (!res.ok) throw new Error('Error al cargar cursos desde API');
                                return res.json();
                            })
                            .then(data => {
                                console.log('loadCourses - Cursos recibidos:', data);
                                this.availableCourses = data;
                            })
                            .catch(error => {
                                console.error("Error en loadCourses:", error);
                                // No mostramos alerta, el select indicará que no hay cursos
                                this.availableCourses = [];
                            })
                            .finally(() => {
                                this.isLoadingCourses = false;
                                console.log('loadCourses - Finalizado.');
                            });
                    },

                    // Se llama cuando cambia el curso seleccionado
                    handleCourseChange() {
                        console.log('handleCourseChange - Curso seleccionado:', this.selectedCourseId);
                        // Resetear instructor y su estado
                        this.selectedInstructorId = '';
                        this.availableInstructors = [];
                        this.instructorOccupied = false;
                        this.loadInstructors(); // Cargar instructores para el nuevo curso
                        this.calculateEndDateAndCheckAvailability(); // Recalcular fecha y verificar disponibilidad
                    },

                    // Carga los instructores asociados al curso seleccionado
                    loadInstructors() {
                        console.log('loadInstructors - Curso ID:', this.selectedCourseId);
                        this.availableInstructors = []; // Limpiar lista
                        if (!this.selectedCourseId) return;

                        this.isLoadingInstructors = true;
                        const url = this.rutasApi.instructoresPorCurso.replace(':cursoId', this.selectedCourseId);
                        console.log('loadInstructors - Fetching:', url);

                        fetch(url)
                            .then(res => {
                                if (!res.ok) {
                                    return res.json().then(err => { throw new Error(err.error || `Error HTTP ${res.status}`) });
                                }
                                return res.json();
                            })
                            .then(data => {
                                console.log('loadInstructors - Instructores recibidos:', data);
                                this.availableInstructors = data;
                            })
                            .catch(error => {
                                console.error("Error en loadInstructors:", error);
                                this.availableInstructors = [];
                            })
                            .finally(() => {
                                this.isLoadingInstructors = false;
                                console.log('loadInstructors - Finalizado.');
                            });
                    },

                    // Dispara el cálculo de fecha fin y la verificación de disponibilidad
                    calculateEndDateAndCheckAvailability() {
                        console.log('calculateEndDateAndCheckAvailability - Disparado por cambio en fecha inicio');
                        this.calculateEndDate();
                        // checkAvailability se llamará desde el finally de calculateEndDate
                    },

                    // Llama a la API para calcular fecha/hora fin
                    calculateEndDate() {
                        this.endDate = ''; // Limpiar
                        this.endTime = '';
                        this.calculatedEndDateText = '';
                        this.showEndDateToast = false;

                        const duration = this.selectedCourseDuration;
                        console.log(`CalculateEndDate: Inicio=${this.startDate}, Duración=${duration}, HoraInicio=${this.startTime}`); // Log C1 modificado

                        if (!this.startDate || !duration || duration <= 0) {
                            console.log("CalculateEndDate: Faltan datos."); // Log C2
                            return;
                        }

                        this.isLoadingEndDate = true;
                        console.log("CalculateEndDate: Llamando a API:", this.rutasApi.calcularFechaFin); // Log C3

                        fetch(this.rutasApi.calcularFechaFin, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json' // Importante
                            },
                            body: JSON.stringify({
                                inicio: this.startDate,
                                horas: duration,
                                hora_inicio: this.startTime
                            })
                        })
                        .then(res => {
                            console.log("CalculateEndDate: Respuesta API status:", res.status); // Log C4
                            if (!res.ok) {
                                return res.json().then(errData => { throw new Error(errData.error || `Error HTTP ${res.status}`) });
                            }
                            return res.json();
                        })
                        .then(data => {
                            console.log("CalculateEndDate: Datos JSON:", data); // Log C5
                            if (data.fecha_fin && data.hora_fin) {
                                this.endDate = data.fecha_fin;
                                this.endTime = data.hora_fin;
                                this.calculatedEndDateText = `${data.fecha_fin} ${data.hora_fin}`; // Usar formato HH:mm
                                this.showEndDateToast = true;
                                console.log("CalculateEndDate: Fechas/Horas actualizadas:", this.endDate, this.endTime); // Log C6
                                setTimeout(() => this.showEndDateToast = false, 3500);
                            } else {
                                 console.error("CalculateEndDate: Respuesta JSON inesperada:", data); // Log C7
                                 alert("No se pudo calcular la fecha/hora de fin.");
                            }
                        })
                        .catch(error => {
                             console.error("CalculateEndDate: ERROR fetch:", error); // Log C8
                             alert(`Error al calcular la fecha de fin: ${error.message}`);
                             // Limpiar fechas si falla el cálculo
                             this.endDate = '';
                             this.endTime = '';
                        })
                        .finally(() => {
                            this.isLoadingEndDate = false;
                            console.log("CalculateEndDate: Fin. Verificando disponibilidad..."); // Log C9
                            // Verificar disponibilidad DESPUÉS de actualizar fechas/horas
                            this.checkAvailability('instructor');
                            this.checkAvailability('aula');
                        });
                    },

                    // Llama a la API para verificar disponibilidad de instructor/aula
                    checkAvailability(type) {
                        const resourceId = type === 'instructor' ? this.selectedInstructorId : this.selectedAulaId;
                        const startDate = this.startDate;
                        const endDate = this.endDate;
                        const startTime = this.startTime;
                        const endTime = this.endTime;

                        console.log(`CheckAvailability (${type}): ResourceID=${resourceId}, Start=${startDate} ${startTime}, End=${endDate} ${endTime}`);

                        // Resetear estado antes de verificar
                        if(type === 'instructor') this.instructorOccupied = false;
                        if(type === 'aula') this.aulaOccupied = false;

                        // Solo verificar si tenemos todos los datos necesarios
                        if (!resourceId || !startDate || !endDate || !startTime || !endTime) {
                            console.log(`CheckAvailability (${type}): Faltan datos, no se verifica.`);
                            return;
                        }

                        const params = new URLSearchParams({
                            tipo: type,
                            id: resourceId,
                            fecha_inicio: startDate,
                            fecha_fin: endDate,
                            hora_inicio: startTime,
                            hora_fin: endTime,
                            // Añadir programacion_id si estamos editando
                            // programacion_id: this.isEditing ? this.programacionId : ''
                        });
                        const url = `${this.rutasApi.verificarDisponibilidad}?${params.toString()}`;
                        console.log(`CheckAvailability (${type}): Fetching:`, url);

                        fetch(url)
                            .then(res => {
                                if (!res.ok) throw new Error(`Error al verificar ${type}`);
                                return res.json();
                            })
                            .then(data => {
                                console.log(`CheckAvailability (${type}): Respuesta Ocupado =`, data.ocupado);
                                if (type === 'instructor') {
                                    this.instructorOccupied = data.ocupado;
                                } else if (type === 'aula') {
                                    this.aulaOccupied = data.ocupado;
                                }
                            })
                            .catch(error => {
                                console.error(`Error en checkAvailability (${type}):`, error);
                                // No bloquear al usuario, pero indicar visualmente que no se pudo verificar?
                                // O mantener en false como está ahora.
                            });
                    },

                    // Abre el modal de disponibilidad y carga datos
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

                                requestAnimationFrame(() => { // Usar requestAnimationFrame
                                    console.log("ModalDisponibilidad (rAF): Intentando renderizar...");
                                    try {
                                        if (typeof window.FullCalendar === 'undefined' || !window.FullCalendar) {
                                            throw new Error('FullCalendar no está definido globalmente.');
                                        }
                                        const currentCalendarEl = document.getElementById('mini-calendar');
                                        if (!currentCalendarEl) {
                                            throw new Error('Elemento #mini-calendar no encontrado.');
                                        }

                                        console.log("ModalDisponibilidad (rAF): Inicializando FullCalendar...");
                                        this.calendarInstance = new window.FullCalendar(currentCalendarEl, {
                                            plugins: [ window.dayGridPlugin, window.interactionPlugin ],
                                            initialView: 'dayGridMonth',
                                            locale: window.esLocale,
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

                                        // Renderizar Tabla
                                        console.log("ModalDisponibilidad (rAF): Intentando renderizar tabla...");
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

                                    } catch (renderError) {
                                        console.error("ModalDisponibilidad (rAF): ¡ERROR al renderizar!", renderError);
                                        if (calendarEl) calendarEl.innerHTML = `<p class="text-red-500 text-center p-4">Error al cargar calendario: ${renderError.message}</p>`;
                                        if (tablaDetalleContenido) tablaDetalleContenido.innerHTML = `<p class="text-red-500 text-center p-4">Error al mostrar detalles.</p>`;
                                        this.isLoadingAvailability = false;
                                    }
                                }); // Fin de requestAnimationFrame
                            })
                            .catch(error => {
                                 console.error(`ModalDisponibilidad: ERROR fetch (${type}):`, error);
                                 this.isLoadingAvailability = false;
                                 if (calendarEl) calendarEl.innerHTML = `<p class="text-red-500 text-center p-4">Error API</p>`;
                                 if (tablaDetalleContenido) tablaDetalleContenido.innerHTML = `<p class="text-red-500 text-sm text-center mt-4">Error al cargar los detalles: ${error.message}</p>`;
                            });
                    }, // Fin openAvailabilityModal

                    // Envía el formulario
                    submitForm(event) {
                        console.log('Validando antes de enviar...');
                        // Validaciones Frontend antes de enviar (Ejemplos)
                        if (!this.selectedGroupId) { alert('Por favor, seleccione un Grupo.'); return; }
                        if (!this.selectedCourseId) { alert('Por favor, seleccione un Curso.'); return; }
                        if (!this.startDate) { alert('Por favor, seleccione una Fecha de Inicio.'); return; }
                        if (!this.endDate) { alert('La Fecha de Fin no se ha calculado. Espere o verifique los datos.'); return; }
                        if (!this.selectedAulaId) { alert('Por favor, seleccione un Aula.'); return; }
                        if (!this.selectedInstructorId) { alert('Por favor, seleccione un Instructor.'); return; }

                        // Advertencias (no impiden enviar, pero avisan)
                        if (this.aulaOccupied) {
                            if (!confirm('ADVERTENCIA: El aula seleccionada parece estar ocupada en este horario. ¿Desea continuar de todas formas?')) return;
                        }
                        if (this.instructorOccupied) {
                             if (!confirm('ADVERTENCIA: El instructor seleccionado parece estar ocupado en este horario. ¿Desea continuar de todas formas?')) return;
                        }

                        console.log('Enviando formulario a:', event.target.action, 'con método:', this.formMethod);
                        event.target.submit(); // Envía el formulario
                    }

                } // Fin del return del objeto Alpine
            } // Fin de la función programacionForm
        </script>

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
                     store: '{{ route('admin.programaciones.store') }}' // Añadir ruta store
                 },
                 csrfToken: '{{ csrf_token() }}'
                 // Pasar datos de edición si es la vista edit.blade.php
                 // isEditing: isset($programacion),
                 // programacionId: $programacion->id ?? null,
                 // initialData: Js::from($programacion ?? null) // Pasar datos existentes
             })"
             x-init="init()"> {{-- Llamar a init al inicializar --}}

            {{-- Contenido del formulario (HTML) --}}
            <div class="flex justify-between items-center mb-6 pb-2 border-b">
                <h1 class="text-2xl font-bold">Programar Curso</h1>
                <a href="{{ route('admin.programaciones.bloque.show') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">
                    📦 Programar por Bloque
                </a>
            </div>

            <x-validation-errors class="mb-4" />

            {{-- El action y method se manejan con Alpine ahora --}}
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
                    {{-- No necesitamos este hidden si calculamos duración desde el select --}}
                    {{-- <input type="hidden" name="duracion_horas" x-bind:value="selectedCourseDuration"> --}}
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

        {{-- Modales y Toast --}}
        <dialog id="modalDisponibilidad" class="w-full max-w-4xl p-0 rounded-lg shadow-lg backdrop:bg-black/40">
             {{-- ... Contenido del modal ... --}}
             <div class="bg-white p-6 relative">
                <button @click="$el.closest('dialog').close()" class="absolute top-2 right-3 text-gray-600 hover:text-gray-900 text-2xl leading-none">×</button>
                <h2 class="text-xl font-bold mb-4" id="modalTitulo">Disponibilidad</h2>
                <div x-show="isLoadingAvailability" x-transition class="text-center py-10">Cargando disponibilidad...</div>
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
             {{-- ... Contenido del modal ... --}}
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

        <div id="toast-fecha-fin" x-show="showEndDateToast" x-cloak x-transition ...>
            📅 Fecha fin estimada: <span x-text="calculatedEndDateText"></span>
        </div>

    </div> {{-- Fin del div py-6 --}}

</x-app-layout>
