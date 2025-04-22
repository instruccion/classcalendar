<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Ordenar Cursos del Bloque – {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coord.' }})
        </h2>
    </x-slot>

    {{-- Script para SortableJS --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    {{-- Script para Carbon-JS (para manejo de fechas/horas en JS) --}}
    <script src="https://cdn.jsdelivr.net/npm/carbon-js@1.8.2/dist/carbon.min.js"></script>


    {{-- ============================================= --}}
    {{--  SCRIPT ALPINE DEFINIDO ANTES DE USARSE      --}}
    {{-- ============================================= --}}
    <script>
        function ordenarBloque(config) {
            return {
                // --- Estado ---
                cursos: config.cursosIniciales.map(c => ({...c, modificado: false })) || [], // Añadir flag modificado
                feriados: new Set(config.feriados || []),
                grupoId: config.grupoId,
                rutaStoreBloque: config.rutaStoreBloque,
                fechaInicioBloque: '',
                horaInicioBloque: '08:30', // Hora de inicio por defecto
                bloqueCodigo: '',
                aulaId: '',
                instructorId: '',
                fechasCalculadas: false,

                // --- Métodos ---
                init() {
                    console.log('Alpine: Ordenar Bloque inicializado.');
                    this.$nextTick(() => {
                        const sortableList = this.$refs.sortableList;
                        if (sortableList && typeof Sortable !== 'undefined' && typeof Carbon !== 'undefined') { // Verificar Carbon también
                            console.log("Alpine: Inicializando SortableJS.");
                            try {
                                Sortable.create(sortableList, {
                                    animation: 150, handle: '.handle', ghostClass: 'bg-blue-100 opacity-50',
                                    onEnd: (evt) => {
                                        const [movedItem] = this.cursos.splice(evt.oldIndex, 1);
                                        this.cursos.splice(evt.newIndex, 0, movedItem);
                                        this.fechasCalculadas = false;
                                        this.cursos.forEach(c => { c.fecha_inicio = ''; c.hora_inicio = ''; c.fecha_fin = ''; c.hora_fin = ''; c.modificado = false; });
                                    }
                                });
                            } catch(e) { console.error("Error inicializando Sortable:", e); }
                        } else { console.error("Alpine: SortableJS, CarbonJS o x-ref='sortableList' no encontrado."); }
                    });
                },

                marcarModificado(event) {
                    const cursoId = event.target.id.split('_')[2];
                    const cursoIndex = this.cursos.findIndex(c => c.id == cursoId);
                    if (cursoIndex > -1) {
                         // Usar $nextTick puede ser necesario si la actualización no se refleja inmediatamente
                         this.$nextTick(() => {
                             this.cursos[cursoIndex].modificado = true;
                             this.fechasCalculadas = false; // Si modifica, ya no son las calculadas
                         });
                    }
                },

                // --- FUNCIÓN DE CÁLCULO CORREGIDA USANDO Carbon-JS ---
                calcularHorariosBloque() {
                    if (!this.fechaInicioBloque) { alert('Seleccione fecha de inicio.'); return; }
                    if (!this.horaInicioBloque) { alert('Seleccione hora de inicio.'); return; }
                    if (this.cursos.length === 0) { alert('No hay cursos.'); return; }
                    if (typeof Carbon === 'undefined') { alert('Error: Librería CarbonJS no cargada.'); return; } // Verificar CarbonJS

                    console.log('Alpine: Calculando horarios JS con Carbon...');
                    const MINUTOS_HORA_ACADEMICA = 50; // Tu requisito
                    const feriados = this.feriados;

                    // Horarios definidos
                    const horaInicioDia = { h: 8, m: 30 };
                    const horaFinManana = { h: 12, m: 0 };
                    const horaInicioTarde = { h: 13, m: 0 };
                    const horaFinDia = { h: 17, m: 0 };

                    let cursor = Carbon.parse(this.fechaInicioBloque + ' ' + this.horaInicioBloque);
                    console.log("Cursor inicial:", cursor.format('YYYY-MM-DD HH:mm'));

                    this.cursos.forEach((curso, index) => {
                        let minutosPendientes = curso.duracion_horas * MINUTOS_HORA_ACADEMICA;
                        console.log(` - Calculando curso ${index + 1}: ${curso.nombre} (${minutosPendientes} min)`);

                        // 1. Ajustar cursor al inicio del día/horario laboral si es necesario
                        cursor = this.ajustarInicioCursorCarbon(cursor, horaInicioDia, feriados);
                        curso.fecha_inicio = cursor.format('YYYY-MM-DD');
                        curso.hora_inicio = cursor.format('HH:mm');
                        curso.modificado = false; // Marcar como no modificado
                        console.log(`   Cursor ajustado para inicio: ${cursor.format('YYYY-MM-DD HH:mm')}`);

                        // 2. Consumir minutos restantes
                        while (minutosPendientes > 0) {
                            cursor = this.ajustarInicioCursorCarbon(cursor, horaInicioDia, feriados); // Reajustar por si saltó día/feriado

                            const finManana = cursor.copy().set({ hour: horaFinManana.h, minute: horaFinManana.m, second: 0 });
                            const inicioTarde = cursor.copy().set({ hour: horaInicioTarde.h, minute: horaInicioTarde.m, second: 0 });
                            const finTarde = cursor.copy().set({ hour: horaFinDia.h, minute: horaFinDia.m, second: 0 });

                            let minutosAUsar = 0;

                            // Intentar asignar en la mañana
                            if (cursor.isBefore(finManana)) {
                                if(cursor.format('HH:mm') < '08:30') cursor.set({ hour: 8, minute: 30 }); // Asegurar inicio 8:30
                                let dispManana = cursor.diffInMinutes(finManana);
                                minutosAUsar = Math.min(minutosPendientes, dispManana);
                                if (minutosAUsar > 0) {
                                    cursor = cursor.addMinutes(minutosAUsar);
                                    minutosPendientes -= minutosAUsar;
                                    console.log(`     + ${minutosAUsar} min (mañana). Restan: ${minutosPendientes}. Cursor: ${cursor.format('HH:mm')}`);
                                }
                            }

                            // Si aún quedan minutos, verificar tarde (saltando almuerzo)
                            if (minutosPendientes > 0) {
                                // Si el cursor quedó en el almuerzo o antes, moverlo a inicio de tarde
                                if (cursor.isBefore(inicioTarde)) {
                                    cursor = inicioTarde.copy();
                                     console.log(`     Cursor movido a inicio de tarde: ${cursor.format('HH:mm')}`);
                                }

                                // Intentar asignar en la tarde
                                if (cursor.isBefore(finTarde)) {
                                     let dispTarde = cursor.diffInMinutes(finTarde);
                                     minutosAUsar = Math.min(minutosPendientes, dispTarde);
                                     if (minutosAUsar > 0) {
                                         cursor = cursor.addMinutes(minutosAUsar);
                                         minutosPendientes -= minutosAUsar;
                                         console.log(`     + ${minutosAUsar} min (tarde). Restan: ${minutosPendientes}. Cursor: ${cursor.format('HH:mm')}`);
                                     }
                                }
                            }

                            // Si todavía quedan minutos, significa que se acabó el día hábil
                            if (minutosPendientes > 0) {
                                console.log(`     Fin día ${cursor.format('YYYY-MM-DD')}. Pasando al siguiente.`);
                                cursor = this.pasarAlSiguienteDiaHabilCarbon(cursor, horaInicioDia, feriados);
                            }
                        } // Fin while minutosPendientes

                        // Asignar fecha/hora de fin
                        curso.fecha_fin = cursor.format('YYYY-MM-DD');
                        curso.hora_fin = cursor.format('HH:mm');
                        console.log(`   * Curso ${index + 1} finaliza: ${curso.fecha_fin} ${curso.hora_fin}`);

                        // Regla adicional: Si termina >= 15:00, el siguiente empieza al otro día
                         if (cursor.hour() >= 15 && index < this.cursos.length - 1) {
                             console.log(`   Terminó >= 15:00, siguiente inicia mañana.`);
                             cursor = this.pasarAlSiguienteDiaHabilCarbon(cursor, horaInicioDia, feriados);
                         }
                         // Si termina justo a las 12:00, el siguiente empieza a las 13:00 del mismo día
                         else if (cursor.hour() === 12 && cursor.minute() === 0 && index < this.cursos.length - 1) {
                              console.log(`   Terminó a las 12:00, siguiente inicia a las 13:00.`);
                              cursor = cursor.set({ hour: 13, minute: 0}); // Mover cursor a las 13:00 para el siguiente
                         }
                         // Si no, el siguiente curso empieza inmediatamente después (el cursor ya está ahí)

                    }); // Fin forEach cursos

                    this.fechasCalculadas = true;
                    console.log('Alpine: Cálculo Carbon de horarios completado.');
                    this.$nextTick(() => console.log('Alpine: Tick después de cálculo Carbon'));
                }, // Fin calcularHorariosBloque

                // --- Helpers usando Carbon-JS ---
                ajustarInicioCursorCarbon(carbonDate, horaInicio, feriados) {
                    let cursor = carbonDate.copy();
                    while (cursor.isWeekend() || feriados.has(cursor.format('YYYY-MM-DD'))) {
                        cursor = cursor.addDay();
                    }
                    const inicioDia = cursor.copy().set({ hour: horaInicio.h, minute: horaInicio.m, second: 0 });
                    const inicioTarde = cursor.copy().set({ hour: 13, minute: 0, second: 0 });
                    const finDia = cursor.copy().set({ hour: 17, minute: 0, second: 0});

                    if (cursor.isBefore(inicioDia)) { // Antes de 8:30
                        return inicioDia;
                    } else if (cursor.hour() === 12) { // Durante almuerzo
                        return inicioTarde;
                    } else if (cursor.isSameOrAfter(finDia)) { // Después de las 17:00
                        return this.pasarAlSiguienteDiaHabilCarbon(cursor, horaInicio, feriados);
                    }
                    return cursor; // Ya está en horario válido
                },

                pasarAlSiguienteDiaHabilCarbon(carbonDate, horaInicio, feriados) {
                    let cursor = carbonDate.copy().addDay();
                    while (cursor.isWeekend() || feriados.has(cursor.format('YYYY-MM-DD'))) {
                        cursor = cursor.addDay();
                    }
                    return cursor.set({ hour: horaInicio.h, minute: horaInicio.m, second: 0 });
                },
                // --- Fin Helpers ---

                submitForm() { /* ... código sin cambios ... */
                    if (!this.fechasCalculadas) { if (!confirm('Horarios no calculados/modificados. ¿Guardar con fechas actuales?')) return; }
                    if (this.cursos.length === 0) { alert('No hay cursos.'); return; }
                    if (!this.aulaId) { alert('Seleccione Aula.'); return; }
                    if (!this.instructorId) { alert('Seleccione Instructor.'); return; }
                    console.log('Alpine: Enviando formulario bloque...');
                    // Añadir aula_id e instructor_id al form si no están como inputs visibles
                     const form = this.$refs.formGuardarBloque;
                     if (!form.querySelector('input[name="aula_id"]')) { let input = document.createElement('input'); input.type = 'hidden'; input.name = 'aula_id'; input.value = this.aulaId; form.appendChild(input); } else { form.querySelector('input[name="aula_id"]').value = this.aulaId; }
                     if (!form.querySelector('input[name="instructor_id"]')) { let input = document.createElement('input'); input.type = 'hidden'; input.name = 'instructor_id'; input.value = this.instructorId; form.appendChild(input); } else { form.querySelector('input[name="instructor_id"]').value = this.instructorId; }
                    // Actualizar inputs ocultos de cursos por si acaso
                     this.cursos.forEach((curso, index) => {
                         form.querySelector(`input[name='cursos[${index}][id]']`).value = curso.id;
                         // Añadir inputs para fechas/horas si no existen (aunque x-model debería bastar)
                          if (!form.querySelector(`input[name='cursos[${index}][fecha_inicio]']`)) {
                              // Podríamos crearlos aquí, pero es mejor asegurar que estén en el template
                              console.warn("Inputs de fecha/hora no encontrados en el form para curso ID:", curso.id);
                          } else {
                             form.querySelector(`input[name='cursos[${index}][fecha_inicio]']`).value = curso.fecha_inicio;
                             form.querySelector(`input[name='cursos[${index}][hora_inicio]']`).value = curso.hora_inicio;
                             form.querySelector(`input[name='cursos[${index}][fecha_fin]']`).value = curso.fecha_fin;
                             form.querySelector(`input[name='cursos[${index}][hora_fin]']`).value = curso.hora_fin;
                         }
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
                <input type="hidden" name="grupo_id" :value="grupoId"> {{-- Corregido a grupoId aquí si lo usas así --}}

                {{-- Sección Superior: Código Bloque, Fecha/Hora Inicio, Aula, Instructor, Botón Calcular --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6 pb-4 border-b items-end">
                    <div>
                        <label for="bloque_codigo" class="block text-sm font-medium text-gray-700 mb-1">Código de Bloque (Opcional)</label>
                        <input type="text" name="bloque_codigo" id="bloque_codigo" x-model="bloqueCodigo"
                               class="w-full border-gray-300 rounded-md shadow-sm py-2"
                               placeholder="Ej: BLQ-{{ $grupo->id }}-{{ date('Y') }}">
                    </div>
                    <div>
                        <label for="fecha_inicio_bloque" class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio Primer Curso <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_inicio_bloque" id="fecha_inicio_bloque" required x-model="fechaInicioBloque"
                               class="w-full border-gray-300 rounded-md shadow-sm py-2">
                    </div>
                     <div>
                        <label for="hora_inicio_bloque" class="block text-sm font-medium text-gray-700 mb-1">Hora Inicio Primer Curso <span class="text-red-500">*</span></label>
                        <input type="time" name="hora_inicio_bloque" id="hora_inicio_bloque" required x-model="horaInicioBloque"
                               class="w-full border-gray-300 rounded-md shadow-sm py-2" value="08:30">
                    </div>
                    <div>
                        <label for="aula_id_bloque" class="block text-sm font-medium text-gray-700 mb-1">Aula para el Bloque <span class="text-red-500">*</span></label>
                        <select name="aula_id" id="aula_id_bloque" required x-model="aulaId"
                                class="w-full border-gray-300 rounded-md shadow-sm py-2">
                            <option value="">Seleccione...</option>
                            @foreach(\App\Models\Aula::where('activa', true)->orderBy('nombre')->get() as $aula)
                                <option value="{{ $aula->id }}">{{ $aula->nombre }} {{ $aula->lugar ? ' - '.$aula->lugar : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                     <div>
                        <label for="instructor_id_bloque" class="block text-sm font-medium text-gray-700 mb-1">Instructor para el Bloque <span class="text-red-500">*</span></label>
                        <select name="instructor_id" id="instructor_id_bloque" required x-model="instructorId"
                                class="w-full border-gray-300 rounded-md shadow-sm py-2">
                            <option value="">Seleccione...</option>
                             @foreach(\App\Models\Instructor::where('activo', true)->orderBy('nombre')->get() as $instructor)
                                <option value="{{ $instructor->id }}">{{ $instructor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                         <button type="button" @click="calcularHorariosBloque" :disabled="!fechaInicioBloque || cursos.length === 0"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <i class="mdi mdi-calculator mr-1"></i> Calcular Horarios
                        </button>
                    </div>
                </div>

                {{-- Lista Reordenable de Cursos --}}
                <h2 class="text-lg font-semibold mb-2 text-gray-800">Cursos en el Bloque (Arrastra <span class="text-xl handle cursor-grab">≡</span> para reordenar)</h2>
                <p x-show="cursos.length === 0" class="text-gray-500">No hay cursos seleccionados.</p>

                <ul x-ref="sortableList" class="space-y-3 mb-6 min-h-[5rem] border rounded-md p-4 bg-gray-50">
                    <template x-for="(curso, index) in cursos" :key="curso.id">
                        <li class="border rounded p-4 bg-white shadow-sm group flex items-start" :data-id="curso.id">
                             {{-- Handle para arrastrar --}}
                             <span class="handle cursor-grab text-gray-400 hover:text-gray-600 mr-3 pt-1">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                             </span>
                             {{-- Contenedor principal del curso --}}
                             <div class="flex-grow">
                                 {{-- Inputs ocultos --}}
                                 <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">
                                 <input type="hidden" :name="`cursos[${index}][orden]`" :value="index">
                                 <input type="hidden" :name="`cursos[${index}][modificado]`" :value="curso.modificado ? '1' : '0'">

                                 {{-- Info Visible --}}
                                 <div class="flex justify-between items-center mb-3">
                                     <strong class="text-indigo-800" x-text="curso.nombre"></strong>
                                     <span class="text-xs text-gray-500" x-text="`(${curso.duracion_horas}h acad.)`"></span>
                                 </div>

                                 {{-- Inputs Editables de Fecha/Hora --}}
                                 <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                     <div>
                                         <label :for="'fecha_inicio_'+curso.id" class="block font-medium text-gray-600 mb-1">F. Inicio</label>
                                         <input type="date" :id="'fecha_inicio_'+curso.id" required :name="`cursos[${index}][fecha_inicio]`"
                                                x-model="curso.fecha_inicio" @change="marcarModificado"
                                                class="w-full border-gray-300 rounded-md shadow-sm py-1.5 text-xs">
                                     </div>
                                     <div>
                                         <label :for="'hora_inicio_'+curso.id" class="block font-medium text-gray-600 mb-1">H. Inicio</label>
                                         <input type="time" :id="'hora_inicio_'+curso.id" required :name="`cursos[${index}][hora_inicio]`"
                                                x-model="curso.hora_inicio" @change="marcarModificado"
                                                class="w-full border-gray-300 rounded-md shadow-sm py-1.5 text-xs">
                                     </div>
                                     <div>
                                         <label :for="'fecha_fin_'+curso.id" class="block font-medium text-gray-600 mb-1">F. Fin</label>
                                         <input type="date" :id="'fecha_fin_'+curso.id" required :name="`cursos[${index}][fecha_fin]`"
                                                x-model="curso.fecha_fin" @change="marcarModificado"
                                                class="w-full border-gray-300 rounded-md shadow-sm py-1.5 text-xs">
                                     </div>
                                     <div>
                                         <label :for="'hora_fin_'+curso.id" class="block font-medium text-gray-600 mb-1">H. Fin</label>
                                         <input type="time" :id="'hora_fin_'+curso.id" required :name="`cursos[${index}][hora_fin]`"
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
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                        <i class="mdi mdi-content-save-all mr-1"></i> Guardar Programación del Bloque
                    </button>
                    <p x-show="cursos.length === 0 || !fechaInicioBloque || !aulaId || !instructorId" class="text-sm text-red-500 mt-2">
                        Asegúrese de seleccionar cursos, fecha de inicio, aula e instructor.
                    </p>
                    {{-- Mensaje opcional si no se han calculado --}}
                    {{-- <p x-show="!fechasCalculadas && cursos.length > 0" class="text-sm text-orange-500 mt-2">Es recomendable calcular los horarios antes de guardar.</p> --}}
                </div>
            </form>

        </div>
    </div>

</x-app-layout>
