<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Ordenar Cursos del Bloque – {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coord.' }})
        </h2>
    </x-slot>

    {{-- Script para SortableJS (Drag and Drop) - Cargar antes de Alpine --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    {{-- Incluir Carbon vía CDN (ya que lo usaremos en JS ahora) - O usar Date nativo --}}
    <script src="https://cdn.jsdelivr.net/npm/carbon-js@1.8.2/dist/carbon.min.js"></script>


    {{-- ============================================= --}}
    {{--  SCRIPT ALPINE DEFINIDO ANTES DE USARSE      --}}
    {{-- ============================================= --}}
    <script>
        function ordenarBloque(config) {
            return {
                // --- Estado ---
                cursos: config.cursosIniciales || [], // Array de {id, nombre, duracion_horas, fecha_inicio, hora_inicio, fecha_fin, hora_fin, modificado}
                feriados: new Set(config.feriados || []), // Usar un Set para búsqueda rápida O(1)
                grupoId: config.grupoId,
                rutaStoreBloque: config.rutaStoreBloque,
                fechaInicioBloque: '',
                bloqueCodigo: '',
                fechasCalculadas: false, // Flag para saber si ya se calcularon

                // --- Métodos ---
                init() {
                    console.log('Alpine: Ordenar Bloque inicializado con', this.cursos.length, 'cursos.');
                    // Inicializar SortableJS en la lista <ul> después de que Alpine cargue el DOM
                    this.$nextTick(() => {
                        const sortableList = this.$refs.sortableList;
                        if (sortableList && typeof Sortable !== 'undefined') {
                            console.log("Alpine: Inicializando SortableJS.");
                            try {
                                Sortable.create(sortableList, {
                                    animation: 150,
                                    handle: '.cursor-grab', // Asegura que el LI tenga esta clase
                                    ghostClass: 'bg-blue-100',
                                    onEnd: (evt) => {
                                        const [movedItem] = this.cursos.splice(evt.oldIndex, 1);
                                        this.cursos.splice(evt.newIndex, 0, movedItem);
                                        console.log('Alpine: Curso movido, nuevo orden:', this.cursos.map(c => c.id));
                                        this.fechasCalculadas = false; // Requerir recalcular
                                        // Limpiar fechas/horas al reordenar
                                        this.cursos.forEach(c => { c.fecha_inicio = ''; c.hora_inicio = ''; c.fecha_fin = ''; c.hora_fin = ''; c.modificado = false; });
                                    }
                                });
                            } catch(e) { console.error("Error inicializando Sortable:", e); }
                        } else if (!sortableList) {
                            console.error("Alpine: Elemento x-ref='sortableList' no encontrado.");
                        } else if (typeof Sortable === 'undefined') {
                            console.error("Alpine: SortableJS no está cargado/definido.");
                        }
                    });
                },

                marcarModificado(event) {
                    const cursoId = event.target.id.split('_')[2]; // Obtener ID (asumiendo id como fecha_inicio_ID)
                    const cursoIndex = this.cursos.findIndex(c => c.id == cursoId);
                    if (cursoIndex > -1) {
                        this.$nextTick(() => { // Asegurar que el valor x-model se actualice primero
                           this.cursos[cursoIndex].modificado = true;
                           this.fechasCalculadas = false; // Marcar como no calculado si se modifica
                           console.log(`Alpine: Curso ID ${cursoId} marcado como modificado.`);
                        });
                    }
                },

                // Calcular horarios usando Carbon-JS
                calcularHorariosBloque() {
                    if (!this.fechaInicioBloque) { alert('Seleccione fecha de inicio.'); return; }
                    if (this.cursos.length === 0) { alert('No hay cursos.'); return; }

                    console.log('Alpine: Calculando horarios JS con Carbon...');
                    const MINUTOS_HORA_ACADEMICA = 50;
                    const feriados = this.feriados; // Ya es un Set

                    // Horarios laborales definidos (en minutos desde medianoche)
                    const horarioMananaInicio = 8 * 60 + 30;
                    const horarioMananaFin = 12 * 60;
                    const horarioTardeInicio = 13 * 60;
                    const horarioTardeFin = 17 * 60;

                    // Inicializar cursor con Carbon-JS
                    let cursor = Carbon.parse(this.fechaInicioBloque + ' 08:30:00');
                    // Ajustar al primer día/hora hábil
                    cursor = this.ajustarInicioCursorCarbon(cursor, { h: 8, m: 30 }, feriados);

                    this.cursos.forEach((curso, index) => {
                        let minutosPendientes = curso.duracion_horas * MINUTOS_HORA_ACADEMICA;
                        console.log(` - Calculando curso ${index + 1}: ${curso.nombre} (${minutosPendientes} min)`);

                        // 1. Ajustar inicio y asignar
                        cursor = this.ajustarInicioCursorCarbon(cursor, { h: 8, m: 30 }, feriados);
                        curso.fecha_inicio = cursor.format('YYYY-MM-DD');
                        curso.hora_inicio = cursor.format('HH:mm');
                        curso.modificado = false;
                        console.log(`   Cursor Carbon inicial ajustado a: ${cursor.format('YYYY-MM-DD HH:mm')}`);

                        // 2. Consumir minutos
                        while (minutosPendientes > 0) {
                            cursor = this.ajustarInicioCursorCarbon(cursor, { h: 8, m: 30 }, feriados); // Reajustar por si saltó día

                            const finManana = cursor.copy().set({ hour: 12, minute: 0, second: 0 });
                            const inicioTarde = cursor.copy().set({ hour: 13, minute: 0, second: 0 });
                            const finTarde = cursor.copy().set({ hour: 17, minute: 0, second: 0 });

                            let minutosAUsar = 0;

                            // ¿Estamos en la mañana?
                            if (cursor.isBefore(finManana)) {
                                // Si es antes de 8:30, mover a 8:30
                                if(cursor.format('HH:mm') < '08:30') cursor.set({ hour: 8, minute: 30 });
                                minutosAUsar = Math.min(minutosPendientes, cursor.diffInMinutes(finManana));
                                if (minutosAUsar > 0) {
                                    cursor = cursor.addMinutes(minutosAUsar);
                                    minutosPendientes -= minutosAUsar;
                                }
                            }
                            // ¿Estamos en la tarde? (verificar después de posible ajuste de mañana)
                            if (minutosPendientes > 0 && cursor.isSameOrAfter(inicioTarde) && cursor.isBefore(finTarde)) {
                                minutosAUsar = Math.min(minutosPendientes, cursor.diffInMinutes(finTarde));
                                if (minutosAUsar > 0) {
                                     cursor = cursor.addMinutes(minutosAUsar);
                                     minutosPendientes -= minutosAUsar;
                                }
                            }

                            // Si no se asignaron minutos o se terminó el día, pasar al siguiente
                             if (minutosPendientes > 0 && (minutosAUsar === 0 || cursor.isSameOrAfter(finTarde))) {
                                 cursor = this.pasarAlSiguienteDiaHabilCarbon(cursor, { h: 8, m: 30 }, feriados);
                             }
                        } // Fin while

                        // Asignar fecha/hora de fin
                        curso.fecha_fin = cursor.format('YYYY-MM-DD');
                        curso.hora_fin = cursor.format('HH:mm');
                        console.log(`   * Curso ${index + 1} Carbon finaliza: ${curso.fecha_fin} ${curso.hora_fin}`);

                        // Regla adicional: Si termina >= 15:00, pasar al día siguiente
                         if (cursor.hour() >= 15 && index < this.cursos.length - 1) {
                             console.log(`   Terminó >= 15:00, siguiente inicia mañana.`);
                             cursor = this.pasarAlSiguienteDiaHabilCarbon(cursor, { h: 8, m: 30 }, feriados);
                         } else if (minutosPendientes <= 0 && cursor.format('HH:mm') >= '12:00' && cursor.format('HH:mm') < '13:00') {
                             // Si terminó justo en el almuerzo, mover cursor a la 1 PM para el siguiente curso
                             cursor.set({ hour: 13, minute: 0 });
                             console.log(`   Terminó en almuerzo, siguiente inicia a las 13:00.`);
                         }


                    }); // Fin forEach

                    this.fechasCalculadas = true;
                    console.log('Alpine: Cálculo Carbon de horarios completado.');
                    this.$nextTick(() => console.log('Alpine: Tick después de cálculo Carbon'));
                },

                // Helper Carbon para ajustar cursor
                ajustarInicioCursorCarbon(carbonDate, horaInicio, feriados) {
                     let cursor = carbonDate.copy();
                     while (cursor.isWeekend() || feriados.has(cursor.format('YYYY-MM-DD'))) {
                         cursor = cursor.addDay();
                     }
                     // Ajustar a la hora de inicio solo si es antes, o si está en hora de almuerzo
                     if (cursor.format('HH:mm') < `${String(horaInicio.h).padStart(2, '0')}:${String(horaInicio.m).padStart(2, '0')}`) {
                          cursor = cursor.set({ hour: horaInicio.h, minute: horaInicio.m, second: 0 });
                     } else if (cursor.hour() === 12) {
                          cursor = cursor.set({ hour: 13, minute: 0, second: 0 });
                     }
                     return cursor;
                 },

                 // Helper Carbon para pasar al siguiente día hábil
                 pasarAlSiguienteDiaHabilCarbon(carbonDate, horaInicio, feriados) {
                      let cursor = carbonDate.copy().addDay();
                      while (cursor.isWeekend() || feriados.has(cursor.format('YYYY-MM-DD'))) {
                          cursor = cursor.addDay();
                      }
                      return cursor.set({ hour: horaInicio.h, minute: horaInicio.m, second: 0 });
                  },

                submitForm() {
                    if (!this.fechasCalculadas) {
                         if (!confirm('Horarios no calculados/modificados. ¿Guardar con fechas actuales?')) return;
                    }
                     if (this.cursos.length === 0) { alert('No hay cursos.'); return; }
                    console.log('Alpine: Enviando formulario bloque...');
                    // Actualizar los inputs hidden antes de enviar (importante si se usa form nativo)
                     this.cursos.forEach((curso, index) => {
                         // Esto podría ser redundante si los inputs se actualizan bien con x-model, pero por seguridad:
                         const form = this.$refs.formGuardarBloque;
                         form.querySelector(`input[name='cursos[${index}][id]']`).value = curso.id;
                         form.querySelector(`input[name='cursos[${index}][fecha_inicio]']`).value = curso.fecha_inicio;
                         form.querySelector(`input[name='cursos[${index}][hora_inicio]']`).value = curso.hora_inicio;
                         form.querySelector(`input[name='cursos[${index}][fecha_fin]']`).value = curso.fecha_fin;
                         form.querySelector(`input[name='cursos[${index}][hora_fin]']`).value = curso.hora_fin;
                     });
                    this.$refs.formGuardarBloque.submit();
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
             cursosIniciales: {{ Js::from($cursosSeleccionados->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre, 'duracion_horas' => $c->duracion_horas, 'fecha_inicio' => '', 'hora_inicio' => '', 'fecha_fin' => '', 'hora_fin' => '', 'modificado' => false])) }},
             feriados: {{ Js::from($feriados ?? []) }},
             grupoId: {{ $grupo->id }},
             rutaStoreBloque: '{{ route('admin.programaciones.bloque.store') }}'
         })" x-init="init()">

        <div class="bg-white p-6 rounded shadow-md">
            {{-- Encabezado y Enlace Volver --}}
            <div class="flex justify-between items-center mb-6 pb-3 border-b">
                 <h1 class="text-2xl font-bold">Paso 2: Ordenar y Programar Bloque</h1>
                 <a href="{{ route('admin.programaciones.bloque.index', ['grupo_id' => $grupo->id]) }}" class="text-blue-600 hover:underline text-sm">
                     ← Volver a Selección
                 </a>
            </div>

            {{-- Formulario Principal --}}
            <form x-ref="formGuardarBloque" method="POST" :action="rutaStoreBloque">
                @csrf
                <input type="hidden" name="grupo_id" :value="grupoId">

                {{-- Sección Superior: Código Bloque, Fecha Inicio, Botón Calcular --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-4 border-b items-end">
                    <div>
                        <label for="bloque_codigo" class="block text-sm font-medium text-gray-700 mb-1">Código de Bloque (Opcional)</label>
                        <input type="text" name="bloque_codigo" id="bloque_codigo" x-model="bloqueCodigo"
                               class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2"
                               placeholder="Ej: BLQ-{{ $grupo->id }}-{{ date('Y') }}">
                    </div>
                    <div>
                        <label for="fecha_inicio_bloque" class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio Primer Curso <span class="text-red-500">*</span></label>
                        <input type="date" id="fecha_inicio_bloque" required x-model="fechaInicioBloque"
                               class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2">
                    </div>
                    <div>
                         <button type="button" @click="calcularHorariosBloque" :disabled="!fechaInicioBloque || cursos.length === 0"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <i class="mdi mdi-calculator mr-1"></i> Calcular Horarios
                        </button>
                    </div>
                </div>

                {{-- Lista Reordenable de Cursos --}}
                <h2 class="text-lg font-semibold mb-2 text-gray-800">Cursos en el Bloque (Arrastra para reordenar)</h2>
                <p x-show="cursos.length === 0" class="text-gray-500">No hay cursos seleccionados.</p>

                <ul x-ref="sortableList" class="space-y-3 mb-6 min-h-[5rem]"> {{-- min-h para dropzone --}}
                    <template x-for="(curso, index) in cursos" :key="curso.id">
                        <li class="border rounded p-4 bg-gray-50 shadow-sm cursor-grab group" :data-id="curso.id">
                            {{-- Inputs ocultos para enviar datos al backend --}}
                            <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">
                            <input type="hidden" :name="`cursos[${index}][orden]`" :value="index"> {{-- Enviar orden --}}
                             <input type="hidden" :name="`cursos[${index}][modificado]`" :value="curso.modificado ? '1' : '0'">

                            {{-- Contenido Visible del Curso --}}
                            <div class="flex justify-between items-center mb-3">
                                <div class="flex items-center">
                                   <span class="inline-flex items-center justify-center w-6 h-6 mr-3 bg-gray-300 text-gray-700 rounded-full text-xs font-bold" x-text="index + 1"></span>
                                   <strong class="text-indigo-800 group-hover:text-indigo-600" x-text="curso.nombre"></strong>
                                </div>
                                <span class="text-xs text-gray-500" x-text="`(${curso.duracion_horas}h acad.)`"></span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                <div>
                                    <label :for="'fecha_inicio_'+curso.id" class="block font-medium text-gray-600 mb-1">Fecha Inicio</label>
                                    <input type="date" :id="'fecha_inicio_'+curso.id" required :name="`cursos[${index}][fecha_inicio]`"
                                           x-model="curso.fecha_inicio" @change="marcarModificado"
                                           class="w-full border-gray-300 rounded-md shadow-sm py-1.5 text-xs">
                                </div>
                                <div>
                                    <label :for="'hora_inicio_'+curso.id" class="block font-medium text-gray-600 mb-1">Hora Inicio</label>
                                    <input type="time" :id="'hora_inicio_'+curso.id" required :name="`cursos[${index}][hora_inicio]`"
                                           x-model="curso.hora_inicio" @change="marcarModificado"
                                           class="w-full border-gray-300 rounded-md shadow-sm py-1.5 text-xs">
                                </div>
                                <div>
                                    <label :for="'fecha_fin_'+curso.id" class="block font-medium text-gray-600 mb-1">Fecha Fin</label>
                                    <input type="date" :id="'fecha_fin_'+curso.id" required :name="`cursos[${index}][fecha_fin]`"
                                           x-model="curso.fecha_fin" @change="marcarModificado"
                                           class="w-full border-gray-300 rounded-md shadow-sm py-1.5 text-xs">
                                </div>
                                <div>
                                    <label :for="'hora_fin_'+curso.id" class="block font-medium text-gray-600 mb-1">Hora Fin</label>
                                    <input type="time" :id="'hora_fin_'+curso.id" required :name="`cursos[${index}][hora_fin]`"
                                           x-model="curso.hora_fin" @change="marcarModificado"
                                           class="w-full border-gray-300 rounded-md shadow-sm py-1.5 text-xs">
                                </div>
                            </div>
                            <p x-show="curso.modificado" class="text-xs text-orange-600 mt-1 italic" x-cloak>Modificado manualmente.</p>
                        </li>
                    </template>
                </ul>

                {{-- Botón Guardar --}}
                <div class="mt-6 pt-6 border-t text-center">
                    <button type="button" @click="submitForm()" :disabled="cursos.length === 0 || !fechasCalculadas"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Guardar Programación del Bloque
                    </button>
                    <p x-show="!fechasCalculadas && cursos.length > 0" class="text-sm text-red-500 mt-2">Es recomendable calcular los horarios antes de guardar.</p>
                </div>
            </form>

        </div>
    </div>

</x-app-layout>
