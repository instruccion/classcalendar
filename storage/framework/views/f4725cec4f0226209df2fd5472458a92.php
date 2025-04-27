<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Programación
        </h2>
     <?php $__env->endSlot(); ?>

    <!-- ✅ FullCalendar 6.1.8 Global Build -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.global.min.js"></script>
    <script>
        window.Calendar = FullCalendar.Calendar;
        window.dayGridPlugin = FullCalendar.DayGridPlugin;
        window.interactionPlugin = FullCalendar.InteractionPlugin;
        window.esLocale = FullCalendar.localesAll['es']; // ← este es el cambio crítico
    </script>

    <div class="py-6 max-w-4xl mx-auto">
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
                    this.calculatedEndDateText = ''; this.showEndDateToast = false;
                    const duration = this.selectedCourseDuration ?? config.duracion_horas;
                    console.log(`Alpine: calculateEndDate - Inicio=${this.startDate}, Duración=${duration}, HoraInicio=${this.startTime}`);
                    if (!this.startDate || !duration || duration <= 0) {
                        console.log("Alpine: calculateEndDate - Faltan datos."); return;
                    }
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
                            horas: duration,
                            hora_inicio: this.startTime
                        })
                    })
                    .then(res => res.ok ? res.json() : res.json().then(err => { throw new Error(err.error || `Error HTTP ${res.status}`) }))
                    .then(data => {
                        console.log("Alpine: calculateEndDate - Datos JSON:", data);
                        if (data.fecha_fin && data.hora_fin) {
                            this.endDate = data.fecha_fin;
                            this.endTime = data.hora_fin;
                            this.calculatedEndDateText = `${data.fecha_fin} ${data.hora_fin}`;
                            this.showEndDateToast = true;
                            console.log("Alpine: calculateEndDate - Fechas/Horas OK:", this.endDate, this.endTime);
                            setTimeout(() => this.showEndDateToast = false, 3500);
                        } else {
                            console.error("Alpine: calculateEndDate - Respuesta inesperada:", data);
                            alert("No se pudo calcular fecha/hora fin.");
                        }
                    })
                    .catch(error => {
                        console.error("Alpine: calculateEndDate ERROR fetch:", error);
                        alert(`Error calculando fecha fin: ${error.message}`);
                        this.endDate = ''; this.endTime = '';
                    })
                    .finally(() => {
                        this.isLoadingEndDate = false;
                        console.log("Alpine: calculateEndDate - Fin. Verificando disponibilidad...");
                        this.checkAvailability?.('instructor');
                        this.checkAvailability?.('aula');
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
                        calendarEl.innerHTML = '';
                        tablaDetalleContenido.innerHTML = '<p class="text-gray-500 text-sm">Cargando detalles...</p>';
                        if (this.calendarInstance) { this.calendarInstance.destroy(); this.calendarInstance = null; }

                        const url = `${this.rutasApi.detalleDisponibilidad}?tipo=${type}&id=${resourceId}`;

                        fetch(url)
                            .then(res => res.ok ? res.json() : res.json().then(err => { throw new Error(err.error || `Error HTTP ${res.status}`) }))
                            .then(data => {
                                requestAnimationFrame(() => {
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

        
        <div class="bg-white p-6 rounded shadow-md"
            x-data="(() => {
                const form = programacionForm({
                    fecha_inicio: '<?php echo e($programacion->fecha_inicio->format('Y-m-d')); ?>',
                    hora_inicio: '<?php echo e($programacion->hora_inicio->format('H:i')); ?>',
                    fecha_fin: '<?php echo e($programacion->fecha_fin->format('Y-m-d')); ?>',
                    hora_fin: '<?php echo e($programacion->hora_fin->format('H:i')); ?>',
                    csrfToken: '<?php echo e(csrf_token()); ?>',
                    ruta_calculo: '<?php echo e(route('admin.api.programaciones.calcularFechaFin')); ?>',
                    duracion_horas: <?php echo e($programacion->curso->duracion_horas ?? 0); ?>

                });

                const extra = editarProgramacion({
                    aula_id: '<?php echo e(old('aula_id', $programacion->aula_id)); ?>',
                    instructor_id: '<?php echo e(old('instructor_id', $programacion->instructor_id)); ?>',
                    rutasApi: {
                        detalleDisponibilidad: '<?php echo e(route('admin.api.programaciones.detalleDisponibilidad')); ?>'
                    }
                });

                return { ...form, ...extra };
            })()">
            <form method="POST"
                  action="<?php echo e(route('admin.programaciones.update', $programacion)); ?>"
                  @submit.prevent="validateBeforeSubmit"
                  class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                
                <div>
                    <label for="grupo_id" class="block font-semibold mb-1">Grupo <span class="text-red-500">*</span></label>
                    <select name="grupo_id" id="grupo_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un grupo...</option>
                        <?php $__currentLoopData = $grupos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($grupo->id); ?>" <?php echo e($programacion->grupo_id == $grupo->id ? 'selected' : ''); ?>>
                                <?php echo e($grupo->nombre); ?> (<?php echo e($grupo->coordinacion?->nombre ?? 'Sin coordinación'); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div>
                    <label for="curso_id" class="block font-semibold mb-1">Curso <span class="text-red-500">*</span></label>
                    <select name="curso_id" id="curso_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un curso...</option>
                        <?php $__currentLoopData = $cursos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $curso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($curso->id); ?>" <?php echo e($programacion->curso_id == $curso->id ? 'selected' : ''); ?>>
                                <?php echo e($curso->nombre); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="md:col-span-2 flex items-center gap-4">
                    <label for="bloque_codigo" class="font-semibold">Bloque</label>
                    <input type="text" name="bloque_codigo" id="bloque_codigo"
                           value="<?php echo e($programacion->bloque_codigo); ?>"
                           class="border px-4 py-2 rounded w-full md:w-1/3">
                </div>

                
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

                
                <div>
                    <label for="aula_id" class="block font-semibold mb-1">Aula</label>
                    <div class="flex gap-2 items-center">
                        <select name="aula_id" id="aula_id"
                                x-model="selectedAulaId"
                                class="w-full border px-4 py-2 rounded bg-white">
                            <option value="">Sin Aula</option>
                            <?php $__currentLoopData = $aulas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($aula->id); ?>" <?php echo e($programacion->aula_id == $aula->id ? 'selected' : ''); ?>>
                                    <?php echo e($aula->nombre); ?><?php echo e($aula->lugar ? ' – ' . $aula->lugar : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button type="button"
                                @click="openAvailabilityModal('aula')"
                                :disabled="!selectedAulaId"
                                class="text-blue-600 hover:text-blue-800 text-xl p-1 disabled:text-gray-400 disabled:cursor-not-allowed"
                                title="Ver disponibilidad del aula">
                            <i class="mdi mdi-calendar-search"></i>
                        </button>
                    </div>
                </div>

                
                <div>
                    <label for="instructor_id" class="block font-semibold mb-1">Instructor</label>
                    <div class="flex gap-2 items-center">
                        <select name="instructor_id" id="instructor_id"
                                x-model="selectedInstructorId"
                                class="w-full border px-4 py-2 rounded bg-white">
                            <option value="">Sin Instructor</option>
                            <?php $__currentLoopData = $instructores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instructor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($instructor->id); ?>" <?php echo e($programacion->instructor_id == $instructor->id ? 'selected' : ''); ?>>
                                    <?php echo e($instructor->nombre); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button type="button"
                                @click="openAvailabilityModal('instructor')"
                                :disabled="!selectedInstructorId"
                                class="text-blue-600 hover:text-blue-800 text-xl p-1 disabled:text-gray-400 disabled:cursor-not-allowed"
                                title="Ver disponibilidad del instructor">
                            <i class="mdi mdi-calendar-search"></i>
                        </button>
                    </div>
                </div>

                
                <div class="md:col-span-2 text-center mt-6">
                    <button type="submit" class="bg-[#00AF40] text-white px-6 py-2 rounded hover:bg-green-700">
                        Guardar Cambios
                    </button>
                </div>
            </form>

            
            <div x-show="showEndDateToast"
                 x-transition
                 x-cloak
                 class="fixed bottom-5 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50"
                 x-init="$watch('showEndDateToast', val => { if (val) setTimeout(() => showEndDateToast = false, 3500) })">
                📅 Fecha fin estimada: <span x-text="calculatedEndDateText"></span>
            </div>
        </div>
    </div>

    
    <dialog id="modalDisponibilidad" class="w-full max-w-4xl p-0 rounded-lg shadow-lg backdrop:bg-black/40"
        x-data="{ isLoadingAvailability: false }"
        x-on:open-modal-availability.window="isLoadingAvailability = true"
        x-on:close-modal-availability.window="isLoadingAvailability = false">
        <div class="bg-white p-6 relative">
            <button onclick="document.getElementById('modalDisponibilidad').close()" class="absolute top-2 right-3 text-gray-600 hover:text-gray-900 text-2xl leading-none">×</button>
            <h2 class="text-xl font-bold mb-4" id="modalTitulo">Disponibilidad</h2>
            <div x-show="isLoadingAvailability" x-transition class="text-center py-10">Cargando disponibilidad...</div>
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
        <div class="bg-white p-6 text-center relative">
            <h2 id="modalEventoTitulo" class="text-xl font-bold text-gray-800 mb-2">Detalle del Evento</h2>
            <div class="flex items-center justify-center gap-3 mb-4">
                <span id="modalEventoColor" class="w-4 h-4 rounded-full inline-block border border-gray-300 flex-shrink-0"></span>
                <p id="modalEventoDescripcion" class="text-gray-700 text-sm text-left"></p>
            </div>
            <button onclick="document.getElementById('modalEventoDetalle').close()" class="mt-4 bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400 transition">
                Cerrar
            </button>
        </div>
    </dialog>



 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/programaciones/edit.blade.php ENDPATH**/ ?>