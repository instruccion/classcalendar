<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Programación
        </h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.global.min.js"></script>
    <script>
        window.FullCalendar = FullCalendar;
        window.dayGridPlugin = FullCalendar.dayGridPlugin;
        window.interactionPlugin = FullCalendar.interactionPlugin;
        window.esLocale = FullCalendar.locales.es;
    </script>


    <div class="py-6 max-w-4xl mx-auto">

        {{-- SCRIPT DE ALPINE --}}
        <script>
            function programacionForm(config) {
                return {
                    startDate: config.fecha_inicio || '',
                    startTime: config.hora_inicio || '08:30',
                    endDate: config.fecha_fin || '',
                    endTime: config.hora_fin || '',
                    csrfToken: config.csrfToken,
                    isLoadingEndDate: false,
                    calculatedEndDateText: '',
                    showEndDateToast: false,

                    calculateEndDate() {
                        this.endDate = ''; this.endTime = '';
                        if (!this.startDate || !config.duracion_horas) return;

                        this.isLoadingEndDate = true;
                        fetch(config.ruta_calculo, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                inicio: this.startDate,
                                hora_inicio: this.startTime,
                                horas: config.duracion_horas
                            })
                        })
                        .then(res => res.ok ? res.json() : res.json().then(err => { throw new Error(err.error) }))
                        .then(data => {
                            this.endDate = data.fecha_fin;
                            this.endTime = data.hora_fin;
                            this.calculatedEndDateText = `${data.fecha_fin} ${data.hora_fin}`;
                            this.showEndDateToast = true;
                        })
                        .catch(error => alert('Error al calcular fecha fin: ' + error.message))
                        .finally(() => {
                            this.isLoadingEndDate = false;
                            setTimeout(() => this.showEndDateToast = false, 4000);
                        });
                    },

                    validateBeforeSubmit(event) {
                        let message = '';

                        if (!document.getElementById('aula_id').value) {
                            message += '⚠️ No se ha seleccionado un aula.\n';
                        }
                        if (!document.getElementById('instructor_id').value) {
                            message += '⚠️ No se ha seleccionado un instructor.\n';
                        }

                        if (message !== '') {
                            this.showToast(message, 'warning');
                        }

                        // Continuar siempre (no bloquea el submit)
                        event.target.submit();
                    },

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
                };
            }

            function editarProgramacion(config) {
                return {
                    selectedAulaId: config.aula_id || '',
                    selectedInstructorId: config.instructor_id || '',
                    rutasApi: config.rutasApi,
                    calendarInstance: null,
                    isLoadingAvailability: false,

                    openAvailabilityModal(type) {
                        const resourceId = type === 'instructor' ? this.selectedInstructorId : this.selectedAulaId;
                        if (!resourceId) return;

                        const resourceName = document.querySelector(`#${type}_id option:checked`)?.textContent || `Recurso ID ${resourceId}`;
                        const modal = document.getElementById('modalDisponibilidad');
                        const calendarEl = document.getElementById('mini-calendar');
                        const tablaDetalleContenido = document.getElementById('tabla-detalle-contenido');

                        if (!modal || !calendarEl || !tablaDetalleContenido || typeof modal.showModal !== 'function') {
                            console.error("Modal o elementos no encontrados."); return;
                        }

                        document.getElementById('modalTitulo').innerHTML = `Disponibilidad de <span class="capitalize font-bold text-gray-900">${type}</span> – <span class="font-bold text-blue-700">${resourceName}</span>`;
                        modal.showModal();
                        this.isLoadingAvailability = true;
                        calendarEl.innerHTML = ''; tablaDetalleContenido.innerHTML = '<p class="text-gray-500 text-sm">Cargando detalles...</p>';
                        if (this.calendarInstance) { this.calendarInstance.destroy(); this.calendarInstance = null; }

                        const url = `${this.rutasApi.detalleDisponibilidad}?tipo=${type}&id=${resourceId}`;

                        fetch(url)
                            .then(res => res.ok ? res.json() : res.json().then(err => { throw new Error(err.error || `Error HTTP ${res.status}`) }))
                            .then(data => {
                                requestAnimationFrame(() => {
                                    this.calendarInstance = new window.FullCalendar.Calendar(calendarEl, {
                                        plugins: [window.dayGridPlugin, window.interactionPlugin],
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
                                            document.getElementById('modalEventoDescripcion').innerHTML = `
                                                <p><strong>Grupo:</strong> ${props.grupo || 'N/A'}</p>
                                                <p><strong>Fechas:</strong> ${props.fecha_inicio_fmt || '?' } - ${props.fecha_fin_fmt || '?'}</p>
                                                <p><strong>Horario:</strong> ${props.hora_inicio_fmt || '?' } - ${props.hora_fin_fmt || '?'}</p>
                                                <p><strong>Coordinación:</strong> ${props.coordinacion || 'N/A'}</p>
                                            `;
                                            document.getElementById('modalEventoDetalle').showModal();
                                        }
                                    });
                                    this.calendarInstance.render();

                                    let tablaHtml = data.tabla?.length
                                        ? `<div class="overflow-x-auto border rounded"><table class="w-full text-xs"><thead><tr><th>Fecha</th><th>Horario</th><th>Curso</th><th>Coordinación</th></tr></thead><tbody>`
                                        : '<p class="text-gray-500 text-sm mt-4">No hay cursos programados.</p>';

                                    if (data.tabla?.length) {
                                        data.tabla.forEach(ev => {
                                            tablaHtml += `<tr><td>${ev.fecha}</td><td>${ev.hora_inicio} - ${ev.hora_fin}</td><td>${ev.curso}</td><td><span style="background-color:${ev.color};" class="inline-block px-1 py-0.5 text-white text-[10px] rounded">${ev.coordinacion}</span></td></tr>`;
                                        });
                                        tablaHtml += '</tbody></table></div>';
                                    }

                                    tablaDetalleContenido.innerHTML = tablaHtml;
                                });
                            })
                            .catch(error => {
                                calendarEl.innerHTML = `<p class="text-red-500 p-4">Error calendario: ${error.message}</p>`;
                                tablaDetalleContenido.innerHTML = `<p class="text-red-500 p-4">Error al cargar detalles.</p>`;
                            })
                            .finally(() => this.isLoadingAvailability = false);
                    }
                };
            }
        </script>


        {{-- FORMULARIO --}}
        <div class="bg-white p-6 rounded shadow-md"
             x-data="programacionForm({
                 fecha_inicio: '{{ $programacion->fecha_inicio->format('Y-m-d') }}',
                 hora_inicio: '{{ $programacion->hora_inicio->format('H:i') }}',
                 fecha_fin: '{{ $programacion->fecha_fin->format('Y-m-d') }}',
                 hora_fin: '{{ $programacion->hora_fin->format('H:i') }}',
                 csrfToken: '{{ csrf_token() }}',
                 ruta_calculo: '{{ route('admin.api.programaciones.calcularFechaFin') }}',
                 duracion_horas: {{ $programacion->curso->duracion_horas ?? 0 }}
             })">

            <form method="POST"
                  action="{{ route('admin.programaciones.update', $programacion) }}"
                  @submit.prevent="validateBeforeSubmit"
                  class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                @csrf
                @method('PUT')

                {{-- GRUPO --}}
                <div>
                    <label for="grupo_id" class="block font-semibold mb-1">Grupo <span class="text-red-500">*</span></label>
                    <select name="grupo_id" id="grupo_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un grupo...</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ $programacion->grupo_id == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin coordinación' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- CURSO --}}
                <div>
                    <label for="curso_id" class="block font-semibold mb-1">Curso <span class="text-red-500">*</span></label>
                    <select name="curso_id" id="curso_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un curso...</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" {{ $programacion->curso_id == $curso->id ? 'selected' : '' }}>
                                {{ $curso->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- BLOQUE --}}
                <div class="md:col-span-2 flex items-center gap-4">
                    <label for="bloque_codigo" class="font-semibold">Bloque</label>
                    <input type="text" name="bloque_codigo" id="bloque_codigo"
                           value="{{ $programacion->bloque_codigo }}"
                           class="border px-4 py-2 rounded w-full md:w-1/3">
                </div>

                {{-- FECHAS Y HORAS --}}
                <div>
                    <label for="fecha_inicio" class="block font-semibold mb-1">Fecha Inicio <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio"
                           x-model="startDate" @change="calculateEndDate"
                           class="w-full border px-4 py-2 rounded" required>
                </div>

                <div>
                    <label for="hora_inicio" class="block font-semibold mb-1">Hora Inicio <span class="text-red-500">*</span></label>
                    <input type="time" name="hora_inicio" id="hora_inicio"
                           x-model="startTime" @change="calculateEndDate"
                           class="w-full border px-4 py-2 rounded" required>
                </div>

                <div>
                    <label for="fecha_fin" class="block font-semibold mb-1">Fecha Fin</label>
                    <input type="date" name="fecha_fin" id="fecha_fin"
                           x-model="endDate"
                           class="w-full border px-4 py-2 rounded bg-gray-100" readonly>
                </div>

                <div>
                    <label for="hora_fin" class="block font-semibold mb-1">Hora Fin</label>
                    <input type="time" name="hora_fin" id="hora_fin"
                           x-model="endTime"
                           class="w-full border px-4 py-2 rounded bg-gray-100" readonly>
                </div>

                {{-- AULA --}}
                <div>
                    <label for="aula_id" class="block font-semibold mb-1">Aula</label>
                    <select name="aula_id" id="aula_id" class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Sin aula</option>
                        @foreach($aulas as $aula)
                            <option value="{{ $aula->id }}" {{ $programacion->aula_id == $aula->id ? 'selected' : '' }}>
                                {{ $aula->nombre }}{{ $aula->lugar ? ' – ' . $aula->lugar : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- INSTRUCTOR --}}
                <div>
                    <label for="instructor_id" class="block font-semibold mb-1">Instructor</label>
                    <select name="instructor_id" id="instructor_id" class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Sin instructor</option>
                        @foreach($instructores as $instructor)
                            <option value="{{ $instructor->id }}" {{ $programacion->instructor_id == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- BOTÓN --}}
                <div class="md:col-span-2 text-center mt-6">
                    <button type="submit" class="bg-[#00AF40] text-white px-6 py-2 rounded hover:bg-green-700">
                        Guardar Cambios
                    </button>
                </div>
            </form>

            {{-- TOAST --}}
            <div x-show="showEndDateToast"
                 x-transition
                 x-cloak
                 class="fixed bottom-5 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50"
                 x-init="$watch('showEndDateToast', val => { if (val) setTimeout(() => showEndDateToast = false, 3500) })">
                📅 Fecha fin estimada: <span x-text="calculatedEndDateText"></span>
            </div>
        </div>
    </div>
</x-app-layout>
