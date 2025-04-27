<x-app-layout>
    <x-slot name="header">
        {{-- Título Adaptado para Edición --}}
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Bloque Programado – {{ $grupo->nombre }} ({{ $bloque_codigo ?: 'Sin Código' }})
        </h2>
    </x-slot>

    {{-- Scripts SortableJS y Carbon-JS (igual que en ordenar) --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/carbon-js@1.8.2/dist/carbon.min.js"></script>

    {{-- ============================================= --}}
    {{--  SCRIPT ALPINE DEFINIDO ANTES DE USARSE      --}}
    {{-- ============================================= --}}
    <script>
        // La función ordenarBloque es la misma que en ordenar.blade.php
        // No necesita cambios internos para la edición, solo recibe diferentes datos iniciales
        function ordenarBloque(config) {
            return {
                // --- Estado (recibe datos existentes) ---
                cursos: config.cursosIniciales || [],
                feriados: new Set(config.feriados || []),
                grupoId: config.grupoId,
                // --- RUTAS ADAPTADAS PARA EDICIÓN ---
                rutaUpdateBloque: config.rutaUpdateBloque, // Nueva ruta para actualizar
                // --- DATOS INICIALES DEL BLOQUE ---
                fechaInicioBloque: config.fechaInicioActual || '',
                horaInicioBloque: config.horaInicioActual || '08:30',
                bloqueCodigo: config.bloqueCodigoActual || '', // Código actual (puede cambiar)
                aulaId: config.aulaActualId || '',
                instructorId: config.instructorActualId || '',
                // --- Otros estados ---
                fechasCalculadas: true, // Asumimos que las fechas iniciales son las "calculadas" o guardadas
                formMethod: 'PUT', // Método para actualizar

                // --- Métodos (Idénticos a ordenar.blade.php) ---
                init() {
                    console.log('Alpine: Editar Bloque inicializado.');
                    this.$nextTick(() => { /* ... inicialización SortableJS ... */
                        const sortableList = this.$refs.sortableList;
                        if (sortableList && typeof Sortable !== 'undefined' && typeof Carbon !== 'undefined') {
                            Sortable.create(sortableList, {
                                animation: 150, handle: '.handle', ghostClass: 'bg-blue-100 opacity-50',
                                onEnd: (evt) => {
                                    const [movedItem] = this.cursos.splice(evt.oldIndex, 1);
                                    this.cursos.splice(evt.newIndex, 0, movedItem);
                                    this.fechasCalculadas = false; // Requerir recalcular si ordena
                                    this.cursos.forEach(c => c.modificado = true ); // Marcar todos como modificados si reordena
                                }
                            });
                        } else { console.error("Alpine: SortableJS, CarbonJS o x-ref no encontrado."); }
                    });
                 },
                marcarModificado(event) { /* ... código idéntico ... */
                    const cursoId = event.target.id.split('_')[2];
                    const cursoIndex = this.cursos.findIndex(c => c.id == cursoId);
                    if (cursoIndex > -1) {
                         this.$nextTick(() => { this.cursos[cursoIndex].modificado = true; this.fechasCalculadas = false; });
                    }
                 },
                calcularHorariosBloque() { /* ... código idéntico para el cálculo con Carbon ... */
                    if (!this.fechaInicioBloque) { alert('Seleccione fecha de inicio.'); return; }
                    if (!this.horaInicioBloque) { alert('Seleccione hora de inicio.'); return; }
                    if (this.cursos.length === 0) { alert('No hay cursos.'); return; }
                    if (typeof Carbon === 'undefined') { alert('Error: CarbonJS no cargado.'); return; }
                    console.log('Alpine: Calculando horarios...');
                    const MINUTOS_HORA_ACADEMICA = 50; const feriados = this.feriados;
                    const horaInicioDia = { h: 8, m: 30 }; const horaFinManana = { h: 12, m: 0 };
                    const horaInicioTarde = { h: 13, m: 0 }; const horaFinDia = { h: 17, m: 0 };
                    let cursor; try { cursor = Carbon.parse(this.fechaInicioBloque + ' ' + this.horaInicioBloque + ':00'); if (!cursor.isValid()) throw new Error(); } catch(e) { alert("Fecha/hora inválida."); return; }
                    this.cursos.forEach((curso, index) => {
                        let minutosPendientes = curso.duracion_horas * MINUTOS_HORA_ACADEMICA;
                        cursor = this.ajustarInicioCursorCarbon(cursor, horaInicioDia, feriados);
                        curso.fecha_inicio = cursor.format('YYYY-MM-DD'); curso.hora_inicio = cursor.format('HH:mm'); curso.modificado = false;
                        while (minutosPendientes > 0) {
                             cursor = this.ajustarInicioCursorCarbon(cursor, horaInicioDia, feriados);
                             const finManana = cursor.copy().set({ hour: horaFinManana.h, minute: horaFinManana.m }); const inicioTarde = cursor.copy().set({ hour: horaInicioTarde.h, minute: horaInicioTarde.m }); const finTarde = cursor.copy().set({ hour: horaFinDia.h, minute: horaFinDia.m });
                             let minutosAUsar = 0;
                             if (cursor.isBefore(finManana)) { let dispManana = Math.max(0, cursor.diffInMinutes(finManana)); minutosAUsar = Math.min(minutosPendientes, dispManana); if (minutosAUsar > 0) { cursor = cursor.addMinutes(minutosAUsar); minutosPendientes -= minutosAUsar; } }
                             if (minutosPendientes > 0) { if (cursor.isBefore(inicioTarde)) cursor = inicioTarde.copy(); if (cursor.isBefore(finTarde)) { let dispTarde = Math.max(0, cursor.diffInMinutes(finTarde)); minutosAUsar = Math.min(minutosPendientes, dispTarde); if (minutosAUsar > 0) { cursor = cursor.addMinutes(minutosAUsar); minutosPendientes -= minutosAUsar; } } }
                             if (minutosPendientes > 0) cursor = this.pasarAlSiguienteDiaHabilCarbon(cursor, horaInicioDia, feriados);
                        }
                        curso.fecha_fin = cursor.format('YYYY-MM-DD'); curso.hora_fin = cursor.format('HH:mm');
                        if (cursor.hour() >= 15 && index < this.cursos.length - 1) cursor = this.pasarAlSiguienteDiaHabilCarbon(cursor, horaInicioDia, feriados);
                        else if (cursor.hour() === 12 && cursor.minute() === 0 && index < this.cursos.length - 1) cursor = cursor.set({ hour: 13, minute: 0});
                    });
                    this.fechasCalculadas = true; console.log('Alpine: Cálculo Carbon completado.'); this.$nextTick(() => console.log('Alpine: Tick post-cálculo'));
                 },
                ajustarInicioCursorCarbon(carbonDate, horaInicio, feriados) { /* ... código idéntico ... */
                     let cursor = carbonDate.copy(); while (cursor.isWeekend() || feriados.has(cursor.format('YYYY-MM-DD'))) { cursor = cursor.addDay(); } const inicioDia = cursor.copy().set({ hour: horaInicio.h, minute: horaInicio.m, second: 0 }); const inicioTarde = cursor.copy().set({ hour: 13, minute: 0, second: 0 }); const finDia = cursor.copy().set({ hour: 17, minute: 0, second: 0}); if (cursor.isBefore(inicioDia)) { return inicioDia; } else if (cursor.hour() === 12) { return inicioTarde; } else if (cursor.isSameOrAfter(finDia)) { return this.pasarAlSiguienteDiaHabilCarbon(cursor, horaInicio, feriados); } return cursor;
                 },
                 pasarAlSiguienteDiaHabilCarbon(carbonDate, horaInicio, feriados) { /* ... código idéntico ... */
                      let cursor = carbonDate.copy().addDay(); while (cursor.isWeekend() || feriados.has(cursor.format('YYYY-MM-DD'))) { cursor = cursor.addDay(); } return cursor.set({ hour: horaInicio.h, minute: horaInicio.m, second: 0 });
                  },
                submitForm() { /* ... código idéntico (pero apunta a rutaUpdateBloque) ... */
                    if (!this.fechasCalculadas && this.cursos.some(c => c.modificado)) { if (!confirm('Ha modificado manualmente algunos horarios y no ha recalculado. ¿Desea guardar estos cambios?')) return; }
                    else if (!this.fechasCalculadas && this.cursos.length > 0) { if (!confirm('Los horarios no han sido calculados o el orden cambió. ¿Desea guardar con las fechas actuales (pueden estar vacías)?')) return; }
                    if (this.cursos.length === 0) { alert('No hay cursos.'); return; } if (!this.aulaId) { alert('Seleccione Aula.'); return; } if (!this.instructorId) { alert('Seleccione Instructor.'); return; }
                    console.log('Alpine: Enviando formulario de ACTUALIZACIÓN bloque...');
                    const form = this.$refs.formGuardarBloque;
                    form.querySelectorAll('input[name^="cursos["]').forEach(el => el.remove()); // Limpiar viejos inputs ocultos
                    this.cursos.forEach((curso, index) => { // Añadir los actuales
                         let idInput = document.createElement('input'); idInput.type = 'hidden'; idInput.name = `cursos[${index}][id]`; idInput.value = curso.id; form.appendChild(idInput);
                         let ordenInput = document.createElement('input'); ordenInput.type = 'hidden'; ordenInput.name = `cursos[${index}][orden]`; ordenInput.value = index; form.appendChild(ordenInput);
                         let fiInput = document.createElement('input'); fiInput.type = 'hidden'; fiInput.name = `cursos[${index}][fecha_inicio]`; fiInput.value = curso.fecha_inicio; form.appendChild(fiInput);
                         let hiInput = document.createElement('input'); hiInput.type = 'hidden'; hiInput.name = `cursos[${index}][hora_inicio]`; hiInput.value = curso.hora_inicio; form.appendChild(hiInput);
                         let ffInput = document.createElement('input'); ffInput.type = 'hidden'; ffInput.name = `cursos[${index}][fecha_fin]`; ffInput.value = curso.fecha_fin; form.appendChild(ffInput);
                         let hfInput = document.createElement('input'); hfInput.type = 'hidden'; hfInput.name = `cursos[${index}][hora_fin]`; hfInput.value = curso.hora_fin; form.appendChild(hfInput);
                         let modInput = document.createElement('input'); modInput.type = 'hidden'; modInput.name = `cursos[${index}][modificado]`; modInput.value = curso.modificado ? '1' : '0'; form.appendChild(modInput);
                    });
                     // Añadir otros campos necesarios para el update
                     // (Aula, Instructor, fecha inicio, hora inicio, codigo bloque)
                     // Se asegura de que los inputs existan o los crea
                    ['aula_id', 'instructor_id', 'fecha_inicio_bloque', 'hora_inicio_bloque', 'bloque_codigo'].forEach(fieldName => {
                        let modelKey = (fieldName === 'aula_id' ? 'aulaId' : (fieldName === 'instructor_id' ? 'instructorId' : fieldName));
                         if (!form.querySelector(`input[name="${fieldName}"]`)) {
                             let input = document.createElement('input'); input.type = 'hidden'; input.name = fieldName; input.value = this[modelKey]; form.appendChild(input);
                         } else { form.querySelector(`input[name="${fieldName}"]`).value = this[modelKey]; }
                    });


                    form.submit();
                 }
            };
        }
    </script>
    {{-- ============================================= --}}
    {{-- FIN SCRIPT ALPINE --}}
    {{-- ============================================= --}}


    {{-- DIV QUE USA EL SCRIPT ALPINE (x-data) --}}
    <div class="py-6 max-w-4xl mx-auto"
         x-data="ordenarBloque({
             {{-- Pasar los datos existentes del bloque desde el controlador --}}
             cursosIniciales: {{ Js::from($cursosParaVista) }},
             feriados: {{ Js::from($feriados ?? []) }},
             grupoId: {{ $grupo->id }},
             {{-- Ruta para ACTUALIZAR --}}
             rutaUpdateBloque: '{{ route('admin.programaciones.bloque.update', ['grupo' => $grupo->id, 'bloque_codigo' => $bloque_codigo]) }}',
             {{-- Valores iniciales para los campos --}}
             fechaInicioActual: '{{ $fechaInicioActual }}',
             horaInicioActual: '{{ $horaInicioActual }}',
             bloqueCodigoActual: '{{ $bloque_codigo }}',
             aulaActualId: '{{ $aulaActualId }}',
             instructorActualId: '{{ $instructorActualId }}'
         })" x-init="init()">

        <div class="bg-white p-6 rounded shadow-md">
            {{-- Encabezado y Enlace Volver --}}
            <div class="flex justify-between items-center mb-6 pb-3 border-b">
                 <h1 class="text-2xl font-bold">Editar Bloque Programado</h1>
                 {{-- Enlace para volver al índice general o al de selección --}}
                 <a href="{{ route('admin.programaciones.index') }}" class="text-blue-600 hover:underline text-sm">
                     ← Volver a Programaciones
                 </a>
            </div>

            {{-- Formulario Principal - Apunta a la ruta UPDATE con método PUT --}}
            <form x-ref="formGuardarBloque" method="POST" :action="rutaUpdateBloque">
                @csrf
                @method('PUT') {{-- Indicar que es una actualización --}}
                {{-- <input type="hidden" name="grupo_id" :value="grupoId"> --}} {{-- El grupo ID ya va en la URL --}}

                {{-- Sección Superior: Código Bloque, Fecha/Hora Inicio, Aula, Instructor, Botón Calcular --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6 pb-4 border-b items-end">
                    <div>
                        <label for="bloque_codigo_input" class="block text-sm font-medium text-gray-700 mb-1">Código de Bloque</label>
                        {{-- Permitir editar el código de bloque --}}
                        <input type="text" name="bloque_codigo_nuevo" id="bloque_codigo_input" x-model="bloqueCodigo"
                               class="w-full border-gray-300 rounded-md shadow-sm py-2"
                               placeholder="Ej: BLQ-{{ $grupo->id }}-{{ date('Y') }}">
                    </div>
                    <div>
                        <label for="fecha_inicio_bloque_input" class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio Primer Curso <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_inicio_bloque" id="fecha_inicio_bloque_input" required x-model="fechaInicioBloque"
                               class="w-full border-gray-300 rounded-md shadow-sm py-2">
                    </div>
                     <div>
                        <label for="hora_inicio_bloque_input" class="block text-sm font-medium text-gray-700 mb-1">Hora Inicio Primer Curso <span class="text-red-500">*</span></label>
                        <input type="time" name="hora_inicio_bloque" id="hora_inicio_bloque_input" required x-model="horaInicioBloque"
                               class="w-full border-gray-300 rounded-md shadow-sm py-2">
                    </div>
                    <div>
                        <label for="aula_id_bloque_select" class="block text-sm font-medium text-gray-700 mb-1">Aula para el Bloque <span class="text-red-500">*</span></label>
                        {{-- Preseleccionar el aula actual --}}
                        <select name="aula_id" id="aula_id_bloque_select" required x-model="aulaId"
                                class="w-full border-gray-300 rounded-md shadow-sm py-2">
                            <option value="">Seleccione...</option>
                            @foreach($aulas as $aula) {{-- Usar $aulas pasado por el controlador --}}
                                <option value="{{ $aula->id }}" >{{ $aula->nombre }} {{ $aula->lugar ? ' - '.$aula->lugar : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                     <div>
                        <label for="instructor_id_bloque_select" class="block text-sm font-medium text-gray-700 mb-1">Instructor para el Bloque <span class="text-red-500">*</span></label>
                        {{-- Preseleccionar el instructor actual --}}
                        <select name="instructor_id" id="instructor_id_bloque_select" required x-model="instructorId"
                                class="w-full border-gray-300 rounded-md shadow-sm py-2">
                            <option value="">Seleccione...</option>
                             @foreach($instructores as $instructor) {{-- Usar $instructores pasado por el controlador --}}
                                <option value="{{ $instructor->id }}">{{ $instructor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                         <button type="button" @click="calcularHorariosBloque" :disabled="!fechaInicioBloque || cursos.length === 0"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <i class="mdi mdi-calculator mr-1"></i> Recalcular Horarios
                        </button>
                    </div>
                </div>

                {{-- Lista Reordenable de Cursos --}}
                <h2 class="text-lg font-semibold mb-2 text-gray-800">Cursos en el Bloque (Arrastra <span class="text-xl handle cursor-grab inline-block align-middle">≡</span> para reordenar)</h2>
                <p x-show="cursos.length === 0" class="text-gray-500">Este bloque está vacío.</p>

                <ul x-ref="sortableList" class="space-y-3 mb-6 min-h-[5rem] border rounded-md p-4 bg-gray-50">
                    <template x-for="(curso, index) in cursos" :key="curso.id">
                        <li class="border rounded p-3 bg-white shadow-sm group flex items-start" :data-id="curso.id">
                             {{-- Handle para arrastrar --}}
                             <span class="handle cursor-grab text-gray-400 hover:text-gray-600 mr-3 pt-1" title="Arrastrar para reordenar">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                             </span>
                             {{-- Contenedor principal del curso --}}
                             <div class="flex-grow">
                                 {{-- Inputs ocultos que se envían al backend --}}
                                 <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">
                                 <input type="hidden" :name="`cursos[${index}][orden]`" :value="index">
                                 <input type="hidden" :name="`cursos[${index}][fecha_inicio]`" :value="curso.fecha_inicio">
                                 <input type="hidden" :name="`cursos[${index}][hora_inicio]`" :value="curso.hora_inicio">
                                 <input type="hidden" :name="`cursos[${index}][fecha_fin]`" :value="curso.fecha_fin">
                                 <input type="hidden" :name="`cursos[${index}][hora_fin]`" :value="curso.hora_fin">
                                 <input type="hidden" :name="`cursos[${index}][modificado]`" :value="curso.modificado ? '1' : '0'">

                                 {{-- Info Visible --}}
                                 <div class="flex justify-between items-center mb-2">
                                     <strong class="text-indigo-800" x-text="curso.nombre"></strong>
                                     <span class="text-xs text-gray-500" x-text="`(${curso.duracion_horas}h acad.)`"></span>
                                 </div>

                                 {{-- Inputs Editables de Fecha/Hora --}}
                                 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                     <div>
                                         <label :for="'edit_fecha_inicio_'+curso.id" class="block font-medium text-gray-600 mb-1">F. Inicio</label>
                                         <input type="date" :id="'edit_fecha_inicio_'+curso.id" required
                                                x-model="curso.fecha_inicio" @change="marcarModificado"
                                                class="w-full border-gray-300 rounded-md shadow-sm py-1.5 text-xs">
                                     </div>
                                     <div>
                                         <label :for="'edit_hora_inicio_'+curso.id" class="block font-medium text-gray-600 mb-1">H. Inicio</label>
                                         <input type="time" :id="'edit_hora_inicio_'+curso.id" required
                                                x-model="curso.hora_inicio" @change="marcarModificado"
                                                class="w-full border-gray-300 rounded-md shadow-sm py-1.5 text-xs">
                                     </div>
                                     <div>
                                         <label :for="'edit_fecha_fin_'+curso.id" class="block font-medium text-gray-600 mb-1">F. Fin</label>
                                         <input type="date" :id="'edit_fecha_fin_'+curso.id" required
                                                x-model="curso.fecha_fin" @change="marcarModificado"
                                                class="w-full border-gray-300 rounded-md shadow-sm py-1.5 text-xs">
                                     </div>
                                     <div>
                                         <label :for="'edit_hora_fin_'+curso.id" class="block font-medium text-gray-600 mb-1">H. Fin</label>
                                         <input type="time" :id="'edit_hora_fin_'+curso.id" required
                                                x-model="curso.hora_fin" @change="marcarModificado"
                                                class="w-full border-gray-300 rounded-md shadow-sm py-1.5 text-xs">
                                     </div>
                                 </div>
                                 <p x-show="curso.modificado" class="text-xs text-orange-600 mt-1 italic" x-cloak>Modificado manualmente.</p>
                            </div>
                        </li>
                    </template>
                </ul>

                {{-- Botón Guardar --}}
                <div class="mt-6 pt-6 border-t text-center">
                    <button type="button" @click="submitForm()" :disabled="cursos.length === 0 || !fechaInicioBloque || !aulaId || !instructorId"
                            class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                        <i class="mdi mdi-content-save-edit mr-1"></i> Actualizar Programación del Bloque
                    </button>
                     <p x-show="cursos.length > 0 && (!fechaInicioBloque || !aulaId || !instructorId)" class="text-sm text-red-500 mt-2">
                        Debe seleccionar Fecha de Inicio, Aula e Instructor.
                    </p>
                     <p x-show="cursos.length === 0" class="text-sm text-red-500 mt-2">
                        No hay cursos en el bloque para guardar.
                    </p>
                </div>
            </form>

        </div>
    </div>

</x-app-layout>
