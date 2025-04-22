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
                cursos: config.cursosIniciales || [],
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
                        if (sortableList && typeof Sortable !== 'undefined') {
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
                        } else { console.error("Alpine: SortableJS o x-ref='sortableList' no encontrado."); }
                    });
                },

                marcarModificado(event) {
                    const cursoId = event.target.id.split('_')[2];
                    const cursoIndex = this.cursos.findIndex(c => c.id == cursoId);
                    if (cursoIndex > -1) {
                        this.$nextTick(() => { this.cursos[cursoIndex].modificado = true; this.fechasCalculadas = false; });
                    }
                },

                // --- FUNCIÓN DE CÁLCULO CORREGIDA ---
                calcularHorariosBloque() {
                    if (!this.fechaInicioBloque) { alert('Seleccione fecha de inicio.'); return; }
                    if (!this.horaInicioBloque) { alert('Seleccione hora de inicio.'); return; }
                    if (this.cursos.length === 0) { alert('No hay cursos.'); return; }

                    console.log('Alpine: Calculando horarios JS...');
                    const MINUTOS_HORA_ACADEMICA = 50;
                    const feriados = this.feriados;
                    const horarioMananaInicio = 8 * 60 + 30; const horarioMananaFin = 12 * 60;
                    const horarioTardeInicio = 13 * 60; const horarioTardeFin = 17 * 60;

                    let cursorTiempo; // Milisegundos
                    try {
                        cursorTiempo = new Date(`${this.fechaInicioBloque}T${this.horaInicioBloque}:00`).getTime();
                        if (isNaN(cursorTiempo)) throw new Error('Fecha/hora inválida');
                    } catch (e) { alert("Fecha u hora de inicio inválida."); return; }

                    this.cursos.forEach((curso, index) => {
                        let minutosPendientes = curso.duracion_horas * MINUTOS_HORA_ACADEMICA;
                        console.log(` - Calculando JS curso ${index + 1}: ${curso.nombre} (${minutosPendientes} min)`);

                        // 1. Ajustar cursor al inicio del día/horario laboral si es necesario
                        cursorTiempo = this.ajustarInicioCursorJS(cursorTiempo, horarioMananaInicio, horarioTardeInicio, feriados);
                        const inicioCursoDate = new Date(cursorTiempo);
                        curso.fecha_inicio = this.formatDateToYMD(inicioCursoDate);
                        curso.hora_inicio = this.formatDateToHM(inicioCursoDate);
                        curso.modificado = false; // Resetear flag
                        console.log(`   Cursor JS inicial ajustado a: ${curso.fecha_inicio} ${curso.hora_inicio}`);

                        // 2. Consumir minutos pendientes
                        while (minutosPendientes > 0) {
                            cursorTiempo = this.ajustarInicioCursorJS(cursorTiempo, horarioMananaInicio, horarioTardeInicio, feriados); // Reajustar por si saltó día
                            let cursorDate = new Date(cursorTiempo);
                            let minutosEnDia = cursorDate.getHours() * 60 + cursorDate.getMinutes();
                            let minutosAUsarEnTramo = 0;

                            // Intentar asignar en la mañana
                            if (minutosEnDia < horarioMananaFin) {
                                let inicioTramoActual = Math.max(minutosEnDia, horarioMananaInicio);
                                let minutosDisponibles = horarioMananaFin - inicioTramoActual;
                                minutosAUsarEnTramo = Math.min(minutosPendientes, minutosDisponibles);
                                if (minutosAUsarEnTramo > 0) {
                                    cursorTiempo += minutosAUsarEnTramo * 60000;
                                    minutosPendientes -= minutosAUsarEnTramo;
                                }
                            }

                            // Si aún quedan minutos, intentar asignar en la tarde
                            if (minutosPendientes > 0) {
                                // Mover cursor a inicio de tarde si es necesario
                                cursorTiempo = this.moverASiguienteTramoOHabil(cursorTiempo, horarioMananaInicio, horarioTardeInicio, feriados);
                                cursorDate = new Date(cursorTiempo); // Actualizar fecha/hora del cursor
                                minutosEnDia = cursorDate.getHours() * 60 + cursorDate.getMinutes();

                                if (minutosEnDia < horarioTardeFin) {
                                     let inicioTramoActual = Math.max(minutosEnDia, horarioTardeInicio);
                                     let minutosDisponibles = horarioTardeFin - inicioTramoActual;
                                     minutosAUsarEnTramo = Math.min(minutosPendientes, minutosDisponibles);
                                     if (minutosAUsarEnTramo > 0) {
                                         cursorTiempo += minutosAUsarEnTramo * 60000;
                                         minutosPendientes -= minutosAUsarEnTramo;
                                     }
                                }
                            }

                             // Si después de ambos tramos aún quedan minutos, pasar al siguiente día hábil
                             if (minutosPendientes > 0) {
                                console.log(`   Fin del día ${new Date(cursorTiempo).toISOString().slice(0,10)}, quedan ${minutosPendientes} min. Pasando al siguiente día.`);
                                cursorTiempo = this.pasarAlSiguienteDiaHabil(cursorTiempo, horarioMananaInicio, feriados);
                             }

                        } // Fin while minutosPendientes

                        // Asignar fecha/hora de fin calculadas
                        const finCursoDate = new Date(cursorTiempo);
                        curso.fecha_fin = this.formatDateToYMD(finCursoDate);
                        curso.hora_fin = this.formatDateToHM(finCursoDate);
                        console.log(`   * Curso ${index + 1} JS finaliza: ${curso.fecha_fin} ${curso.hora_fin}`);

                        // --- REGLA ADICIONAL: Si termina >= 15:00, el siguiente empieza al otro día ---
                         if (finCursoDate.getHours() >= 15 && index < this.cursos.length - 1) {
                             console.log(`   Terminó >= 15:00, siguiente curso inicia mañana.`);
                             cursorTiempo = this.pasarAlSiguienteDiaHabil(cursorTiempo, horarioMananaInicio, feriados);
                         }
                         // Mover cursor para inicio del siguiente curso si terminó en almuerzo
                         else if (finCursoDate.getHours() === 12 && finCursoDate.getMinutes() === 0 && index < this.cursos.length - 1) {
                             cursorTiempo = finCursoDate.setHours(13,0,0,0);
                             console.log(`   Terminó al mediodía, siguiente curso inicia a las 13:00.`);
                         }
                         // Si no aplica la regla de las 3pm ni almuerzo, el siguiente curso empieza justo después
                         // (el cursor ya está en la posición correcta)

                    }); // Fin forEach cursos

                    this.fechasCalculadas = true;
                    console.log('Alpine: Cálculo JS de horarios completado.');
                    this.$nextTick(() => console.log('Alpine: Tick después de cálculo JS'));
                }, // Fin calcularHorariosBloque

                // Helper JS para ajustar el cursor al inicio del día/horario laboral
                 ajustarInicioCursorJS(timestamp, inicioDiaMinutos, inicioTardeMinutos, feriados) {
                     let cursor = new Date(timestamp);
                     let diaSemana = cursor.getDay();
                     let fechaYMD = this.formatDateToYMD(cursor);

                     // Saltar fines de semana y feriados
                     while (diaSemana === 0 || diaSemana === 6 || feriados.has(fechaYMD)) {
                         cursor.setDate(cursor.getDate() + 1);
                         diaSemana = cursor.getDay();
                         fechaYMD = this.formatDateToYMD(cursor);
                     }

                     // Ajustar hora
                     let currentMinutos = cursor.getHours() * 60 + cursor.getMinutes();
                     // Si es antes de 8:30
                     if (currentMinutos < inicioDiaMinutos) {
                          cursor.setHours(Math.floor(inicioDiaMinutos / 60), inicioDiaMinutos % 60, 0, 0);
                     }
                     // Si está en hora de almuerzo (12:00 a 12:59)
                     else if (cursor.getHours() === 12) {
                          cursor.setHours(Math.floor(inicioTardeMinutos / 60), inicioTardeMinutos % 60, 0, 0);
                     }
                      // Si es después de las 17:00, pasar al día siguiente
                      else if (currentMinutos >= 17 * 60) {
                           return this.pasarAlSiguienteDiaHabil(timestamp, inicioDiaMinutos, feriados);
                      }

                     return cursor.getTime();
                 },

                 // Helper JS para pasar al siguiente día hábil a las 8:30
                 pasarAlSiguienteDiaHabil(timestamp, inicioDiaMinutos, feriados) {
                      let cursor = new Date(timestamp);
                      cursor.setDate(cursor.getDate() + 1);
                      cursor.setHours(Math.floor(inicioDiaMinutos / 60), inicioDiaMinutos % 60, 0, 0); // Poner hora inicio por defecto
                       // Saltar findes/feriados desde el nuevo día
                      while ([0, 6].includes(cursor.getDay()) || feriados.has(this.formatDateToYMD(cursor))) {
                          cursor.setDate(cursor.getDate() + 1);
                      }
                      return cursor.getTime();
                  },

                 // Helper JS para formatear Fecha YYYY-MM-DD
                 formatDateToYMD(date) { /* ...código sin cambios... */ },

                 // Helper JS para formatear Hora HH:MM
                 formatDateToHM(date) { /* ...código sin cambios... */ },

                submitForm() { /* ...código sin cambios... */ }
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
