<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Ordenar Cursos del Bloque – {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coord.' }})
        </h2>
    </x-slot>

    {{-- Script para SortableJS --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    {{-- Script para Carbon-JS --}}
    <script src="https://cdn.jsdelivr.net/npm/carbon-js@1.8.2/dist/carbon.min.js"></script>

    {{-- ============================================= --}}
    {{--  SCRIPT ALPINE DEFINIDO ANTES DE USARSE      --}}
    {{-- ============================================= --}}
    <script>
        function ordenarBloque(config) {
            return {
                // --- Estado ---
                cursos: config.cursosIniciales.map(c => ({...c, modificado: false, fecha_inicio: '', hora_inicio: '', fecha_fin: '', hora_fin: '' })) || [], // Asegurar que las propiedades existan y modificado=false
                feriados: new Set(config.feriados || []),
                grupoId: config.grupoId, // Corregido: usar la variable pasada
                rutaStoreBloque: config.rutaStoreBloque,
                fechaInicioBloque: '',
                horaInicioBloque: '08:30', // Añadido para la hora de inicio
                bloqueCodigo: '',
                aulaId: '',         // Añadido
                instructorId: '',   // Añadido
                fechasCalculadas: false,

                // --- Métodos ---
                init() {
                    console.log('Alpine: Ordenar Bloque inicializado.');
                    this.$nextTick(() => {
                        const sortableList = this.$refs.sortableList;
                        if (sortableList && typeof Sortable !== 'undefined' && typeof Carbon !== 'undefined') {
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
                         this.$nextTick(() => { this.cursos[cursoIndex].modificado = true; this.fechasCalculadas = false; });
                    }
                },

                // --- FUNCIÓN DE CÁLCULO CORREGIDA USANDO Carbon-JS ---
                calcularHorariosBloque() {
                    if (!this.fechaInicioBloque) { alert('Seleccione fecha de inicio.'); return; }
                    if (!this.horaInicioBloque) { alert('Seleccione hora de inicio.'); return; }
                    if (this.cursos.length === 0) { alert('No hay cursos.'); return; }
                    if (typeof Carbon === 'undefined') { alert('Error: Librería CarbonJS no cargada.'); return; }

                    console.log('Alpine: Calculando horarios JS con Carbon...');
                    const MINUTOS_HORA_ACADEMICA = 50; // Tu requisito
                    const feriados = this.feriados;
                    const horaInicioDia = { h: 8, m: 30 };
                    const horaFinManana = { h: 12, m: 0 };
                    const horaInicioTarde = { h: 13, m: 0 };
                    const horaFinDia = { h: 17, m: 0 };

                    // Inicializar cursor con Carbon-JS desde los inputs del usuario
                    let cursor;
                    try {
                        cursor = Carbon.parse(this.fechaInicioBloque + ' ' + this.horaInicioBloque + ':00'); // Añadir segundos
                         if (!cursor.isValid()) throw new Error('Fecha/hora inválida');
                    } catch(e) {
                         console.error("Error parseando fecha/hora inicio:", e);
                         alert("Fecha u hora de inicio inválida.");
                         return;
                    }

                    console.log("Cursor inicial:", cursor.format('YYYY-MM-DD HH:mm'));

                    // Iterar sobre los cursos EN EL ORDEN ACTUAL DEL ARRAY this.cursos
                    this.cursos.forEach((curso, index) => {
                        let minutosPendientes = curso.duracion_horas * MINUTOS_HORA_ACADEMICA;
                        console.log(` - Calculando curso ${index + 1}: ${curso.nombre} (${minutosPendientes} min)`);

                        // 1. Ajustar cursor al inicio del día/horario laboral si es necesario
                        cursor = this.ajustarInicioCursorCarbon(cursor, horaInicioDia, feriados);
                        curso.fecha_inicio = cursor.format('YYYY-MM-DD');
                        curso.hora_inicio = cursor.format('HH:mm');
                        curso.modificado = false; // Resetear flag
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
                                 let dispManana = Math.max(0, cursor.diffInMinutes(finManana)); // Asegurar >= 0
                                 minutosAUsar = Math.min(minutosPendientes, dispManana);
                                 if (minutosAUsar > 0) {
                                     cursor = cursor.addMinutes(minutosAUsar);
                                     minutosPendientes -= minutosAUsar;
                                     console.log(`     + ${minutosAUsar} min (mañana). Restan: ${minutosPendientes}. Cursor: ${cursor.format('HH:mm')}`);
                                 }
                             }

                             // Si aún quedan minutos, verificar tarde (saltando almuerzo)
                             if (minutosPendientes > 0) {
                                 // Si el cursor quedó antes de las 13:00 (en almuerzo o antes), moverlo a las 13:00
                                 if (cursor.isBefore(inicioTarde)) {
                                     cursor = inicioTarde.copy();
                                     console.log(`     Cursor movido a inicio de tarde: ${cursor.format('HH:mm')}`);
                                 }

                                 // Intentar asignar en la tarde
                                 if (cursor.isBefore(finTarde)) {
                                      let dispTarde = Math.max(0, cursor.diffInMinutes(finTarde));
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

                        // --- REGLA ADICIONAL: Si termina >= 15:00, el siguiente empieza al otro día ---
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
                         console.log(`   Ajuste: Saltando ${cursor.format('YYYY-MM-DD')} (finde o feriado)`);
                         cursor = cursor.addDay();
                     }
                     const inicioDia = cursor.copy().set({ hour: horaInicio.h, minute: horaInicio.m, second: 0 });
                     const inicioTarde = cursor.copy().set({ hour: 13, minute: 0, second: 0 });
                     const finDia = cursor.copy().set({ hour: 17, minute: 0, second: 0});

                     if (cursor.isBefore(inicioDia)) { // Antes de 8:30
                         console.log(`   Ajuste: Moviendo a inicio de día ${inicioDia.format('HH:mm')}`);
                         return inicioDia;
                     } else if (cursor.hour() === 12) { // Durante almuerzo
                          console.log(`   Ajuste: Moviendo a inicio de tarde ${inicioTarde.format('HH:mm')}`);
                         return inicioTarde;
                     } else if (cursor.isSameOrAfter(finDia)) { // Después de las 17:00
                         console.log(`   Ajuste: Pasando al siguiente día hábil desde ${cursor.format('YYYY-MM-DD HH:mm')}`);
                         return this.pasarAlSiguienteDiaHabilCarbon(cursor, horaInicio, feriados);
                     }
                     return cursor; // Ya está en horario válido
                 },

                 pasarAlSiguienteDiaHabilCarbon(carbonDate, horaInicio, feriados) {
                      let cursor = carbonDate.copy().addDay(); // Ir al día siguiente
                      while (cursor.isWeekend() || feriados.has(cursor.format('YYYY-MM-DD'))) {
                          cursor = cursor.addDay();
                      }
                      // Establecer la hora de inicio
                      return cursor.set({ hour: horaInicio.h, minute: horaInicio.m, second: 0 });
                  },
                  // --- Fin Helpers ---

                submitForm() {
                    if (!this.fechasCalculadas) {
                         if (!confirm('Horarios no calculados o modificados. ¿Guardar con fechas actuales?')) return;
                    }
                     if (this.cursos.length === 0) { alert('No hay cursos.'); return; }
                     if (!this.aulaId) { alert('Seleccione Aula.'); return; }
                     if (!this.instructorId) { alert('Seleccione Instructor.'); return; }

                     console.log('Alpine: Enviando formulario bloque...');
                     const form = this.$refs.formGuardarBloque;
                     // Asegurarse que los inputs ocultos para IDs y orden estén actualizados
                     // Limpiar inputs de fechas/horas existentes antes de añadir los nuevos
                     form.querySelectorAll('input[name^="cursos["]').forEach(el => el.remove());
                     // Añadir inputs actualizados
                     this.cursos.forEach((curso, index) => {
                        let idInput = document.createElement('input'); idInput.type = 'hidden'; idInput.name = `cursos[${index}][id]`; idInput.value = curso.id; form.appendChild(idInput);
                        let ordenInput = document.createElement('input'); ordenInput.type = 'hidden'; ordenInput.name = `cursos[${index}][orden]`; ordenInput.value = index; form.appendChild(ordenInput);
                        let fiInput = document.createElement('input'); fiInput.type = 'hidden'; fiInput.name = `cursos[${index}][fecha_inicio]`; fiInput.value = curso.fecha_inicio; form.appendChild(fiInput);
                        let hiInput = document.createElement('input'); hiInput.type = 'hidden'; hiInput.name = `cursos[${index}][hora_inicio]`; hiInput.value = curso.hora_inicio; form.appendChild(hiInput);
                        let ffInput = document.createElement('input'); ffInput.type = 'hidden'; ffInput.name = `cursos[${index}][fecha_fin]`; ffInput.value = curso.fecha_fin; form.appendChild(ffInput);
                        let hfInput = document.createElement('input'); hfInput.type = 'hidden'; hfInput.name = `cursos[${index}][hora_fin]`; hfInput.value = curso.hora_fin; form.appendChild(hfInput);
                        let modInput = document.createElement('input'); modInput.type = 'hidden'; modInput.name = `cursos[${index}][modificado]`; modInput.value = curso.modificado ? '1' : '0'; form.appendChild(modInput);
                     });
                     // Añadir inputs para los selectores del bloque
                    if (!form.querySelector('input[name="aula_id"]')) { let input = document.createElement('input'); input.type = 'hidden'; input.name = 'aula_id'; input.value = this.aulaId; form.appendChild(input); } else { form.querySelector('input[name="aula_id"]').value = this.aulaId; }
                    if (!form.querySelector('input[name="instructor_id"]')) { let input = document.createElement('input'); input.type = 'hidden'; input.name = 'instructor_id'; input.value = this.instructorId; form.appendChild(input); } else { form.querySelector('input[name="instructor_id"]').value = this.instructorId; }
                    if (!form.querySelector('input[name="fecha_inicio_bloque"]')) { let input = document.createElement('input'); input.type = 'hidden'; input.name = 'fecha_inicio_bloque'; input.value = this.fechaInicioBloque; form.appendChild(input); } else { form.querySelector('input[name="fecha_inicio_bloque"]').value = this.fechaInicioBloque; }
                    if (!form.querySelector('input[name="hora_inicio_bloque"]')) { let input = document.createElement('input'); input.type = 'hidden'; input.name = 'hora_inicio_bloque'; input.value = this.horaInicioBloque; form.appendChild(input); } else { form.querySelector('input[name="hora_inicio_bloque"]').value = this.horaInicioBloque; }
                    if (!form.querySelector('input[name="bloque_codigo"]')) { let input = document.createElement('input'); input.type = 'hidden'; input.name = 'bloque_codigo'; input.value = this.bloqueCodigo; form.appendChild(input); } else { form.querySelector('input[name="bloque_codigo"]').value = this.bloqueCodigo; }


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
                {{-- El input oculto de grupo_id se maneja en submitForm --}}
                {{-- <input type="hidden" name="grupo_id" :value="grupoId"> --}}

                {{-- Sección Superior: Código Bloque, Fecha/Hora Inicio, Aula, Instructor, Botón Calcular --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6 pb-4 border-b items-end">
                    <div>
                        <label for="bloque_codigo_input" class="block text-sm font-medium text-gray-700 mb-1">Código de Bloque (Opcional)</label>
                        <input type="text" id="bloque_codigo_input" x-model="bloqueCodigo"
                               class="w-full border-gray-300 rounded-md shadow-sm py-2"
                               placeholder="Ej: BLQ-{{ $grupo->id }}-{{ date('Y') }}">
                    </div>
                    <div>
                        <label for="fecha_inicio_bloque_input" class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio Primer Curso <span class="text-red-500">*</span></label>
                        <input type="date" id="fecha_inicio_bloque_input" required x-model="fechaInicioBloque"
                               class="w-full border-gray-300 rounded-md shadow-sm py-2">
                    </div>
                     <div>
                        <label for="hora_inicio_bloque_input" class="block text-sm font-medium text-gray-700 mb-1">Hora Inicio Primer Curso <span class="text-red-500">*</span></label>
                        <input type="time" id="hora_inicio_bloque_input" required x-model="horaInicioBloque"
                               class="w-full border-gray-300 rounded-md shadow-sm py-2" value="08:30">
                    </div>
                    <div>
                        <label for="aula_id_bloque_select" class="block text-sm font-medium text-gray-700 mb-1">Aula para el Bloque <span class="text-red-500">*</span></label>
                        <select id="aula_id_bloque_select" required x-model="aulaId"
                                class="w-full border-gray-300 rounded-md shadow-sm py-2">
                            <option value="">Seleccione...</option>
                            {{-- Cargar Aulas Activas --}}
                            @foreach(\App\Models\Aula::where('activa', true)->orderBy('nombre')->get() as $aula)
                                <option value="{{ $aula->id }}">{{ $aula->nombre }} {{ $aula->lugar ? ' - '.$aula->lugar : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                     <div>
                        <label for="instructor_id_bloque_select" class="block text-sm font-medium text-gray-700 mb-1">Instructor para el Bloque <span class="text-red-500">*</span></label>
                        <select id="instructor_id_bloque_select" required x-model="instructorId"
                                class="w-full border-gray-300 rounded-md shadow-sm py-2">
                            <option value="">Seleccione...</option>
                            {{-- Cargar Instructores Activos --}}
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
                <h2 class="text-lg font-semibold mb-2 text-gray-800">Cursos en el Bloque (Arrastra <span class="text-xl handle cursor-grab inline-block align-middle">≡</span> para reordenar)</h2>
                <p x-show="cursos.length === 0" class="text-gray-500">No hay cursos seleccionados.</p>

                <ul x-ref="sortableList" class="space-y-3 mb-6 min-h-[5rem] border rounded-md p-4 bg-gray-50">
                    <template x-for="(curso, index) in cursos" :key="curso.id">
                        <li class="border rounded p-3 bg-white shadow-sm group flex items-start" :data-id="curso.id">
                             {{-- Handle para arrastrar --}}
                             <span class="handle cursor-grab text-gray-400 hover:text-gray-600 mr-3 pt-1" title="Arrastrar para reordenar">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                             </span>
                             {{-- Contenedor principal del curso --}}
                             <div class="flex-grow">
                                 {{-- Inputs ocultos que SÍ se envían --}}
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

                                 {{-- Fechas/Horas mostradas (no son inputs de formulario) --}}
                                 <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 text-xs text-gray-600">
                                     <div>
                                         <span class="font-medium">Inicio:</span>
                                         <span x-text="curso.fecha_inicio ? `${curso.fecha_inicio} ${curso.hora_inicio}` : 'N/C'"></span>
                                     </div>
                                     <div>
                                         <span class="font-medium">Fin:</span>
                                         <span x-text="curso.fecha_fin ? `${curso.fecha_fin} ${curso.hora_fin}` : 'N/C'"></span>
                                     </div>
                                 </div>
                                 <p x-show="curso.modificado" class="text-xs text-orange-600 mt-1 italic" x-cloak>Fechas/horas modificadas manualmente (no implementado aún).</p>
                             </div>
                        </li>
                    </template>
                </ul>

                {{-- Botón Guardar --}}
                <div class="mt-6 pt-6 border-t text-center">
                    <button type="button" @click="submitForm()" :disabled="cursos.length === 0 || !fechaInicioBloque || !aulaId || !instructorId || !fechasCalculadas"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                        <i class="mdi mdi-content-save-all mr-1"></i> Guardar Programación del Bloque
                    </button>
                    <p x-show="cursos.length > 0 && (!fechaInicioBloque || !aulaId || !instructorId)" class="text-sm text-red-500 mt-2">
                        Debe seleccionar Fecha de Inicio, Aula e Instructor.
                    </p>
                     <p x-show="cursos.length > 0 && fechaInicioBloque && aulaId && instructorId && !fechasCalculadas" class="text-sm text-orange-500 mt-2">
                        Necesita calcular los horarios antes de guardar.
                    </p>
                     <p x-show="cursos.length === 0" class="text-sm text-red-500 mt-2">
                        No hay cursos en el bloque para guardar.
                    </p>
                </div>
            </form>

        </div>
    </div>

</x-app-layout>
