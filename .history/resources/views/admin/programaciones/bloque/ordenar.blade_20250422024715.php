<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Ordenar Cursos del Bloque – {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coord.' }})
        </h2>
    </x-slot>

    {{-- Script para SortableJS (Drag and Drop) - Cargar antes de Alpine --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <div class="py-6 max-w-4xl mx-auto"
        x-data="ordenarBloque({
            {{-- Pasar cursos con ID, nombre y duración - CORREGIDO --}}
            cursosIniciales: {{ Js::from($cursosSeleccionados->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre, 'duracion_horas' => $c->duracion_horas, 'fecha_inicio' => '', 'hora_inicio' => '', 'fecha_fin' => '', 'hora_fin' => ''])) }},
            feriados: {{ Js::from($feriados ?? []) }},
            grupoId: {{ $grupo->id }},
            rutaStoreBloque: '{{ route('admin.programaciones.bloque.store') }}' // Ruta para guardar
        })">

        <div class="bg-white p-6 rounded shadow-md">
            <div class="flex justify-between items-center mb-6 pb-3 border-b">
                 <h1 class="text-2xl font-bold">Paso 2: Ordenar y Programar Bloque</h1>
                 <a href="{{ route('admin.programaciones.bloque.index', ['grupo_id' => $grupo->id]) }}" class="text-blue-600 hover:underline text-sm">
                     ← Volver a Selección
                 </a>
            </div>

            <form x-ref="formGuardarBloque" method="POST" :action="rutaStoreBloque">
                @csrf
                <input type="hidden" name="grupo_id" :value="grupoId">

                {{-- Sección Superior: Código Bloque y Fecha Inicio --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-4 border-b">
                    <div class="md:col-span-1">
                        <label for="bloque_codigo" class="block text-sm font-medium text-gray-700 mb-1">Código de Bloque (Opcional)</label>
                        <input type="text" name="bloque_codigo" id="bloque_codigo"
                               x-model="bloqueCodigo"
                               class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2"
                               placeholder="Ej: BLQ-TIERRA-01">
                    </div>
                    <div class="md:col-span-1">
                        <label for="fecha_inicio_bloque" class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio Primer Curso <span class="text-red-500">*</span></label>
                        <input type="date" id="fecha_inicio_bloque" required
                               x-model="fechaInicioBloque"
                               class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2">
                    </div>
                    <div class="md:col-span-1 flex items-end pb-1">
                         <button type="button" @click="calcularHorariosBloque" :disabled="!fechaInicioBloque || cursos.length === 0"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <i class="mdi mdi-calculator mr-1"></i> Calcular Horarios
                        </button>
                    </div>
                </div>

                 {{-- Lista Reordenable de Cursos --}}
                 <h2 class="text-lg font-semibold mb-2 text-gray-800">Cursos en el Bloque (Arrastra para reordenar)</h2>
                 <p x-show="cursos.length === 0" class="text-gray-500">No hay cursos seleccionados.</p>

                 <ul x-ref="sortableList" class="space-y-3 mb-6">
                     {{-- El template ahora genera inputs ocultos y visibles --}}
                     <template x-for="(curso, index) in cursos" :key="curso.id">
                         <li class="borderEntendido perfectamente! Quieres que la vista `ordenar.blade.php` no solo muestre la lista de cursos seleccionados, sino que también:

1.  **Permita al usuario ingresar:**
    *   Un código de bloque (opcional).
    *   La fecha de inicio del *primer* curso del bloque.
    *   (Opcionalmente) La hora de inicio del primer curso (o asumir 8:30).
    *   (Opcionalmente, pero necesario para guardar) Un Aula y un Instructor para **todo el bloque**. *Esto es una simplificación inicial, podríamos hacerlo más complejo luego si un bloque puede tener diferentes aulas/instructores*.
2.  **Tenga un botón "Calcular Fechas"** que, al presionarlo:
    *   Use la fecha/hora de inicio proporcionada.
    *   Itere sobre los cursos en el orden actual de la lista.
    *   Para cada curso, calcule su fecha/hora de inicio y fin **reales** basándose en la finalización del anterior, la duración del curso (horas * 50 minutos), el horario laboral (8:30-12:00, 13:00-17:00), saltando fines de semana y feriados.
    *   Actualice los campos de fecha/hora inicio y fin para cada curso en la lista.
3.  **Permita Reordenar:** Que el usuario pueda cambiar el orden de los cursos en la lista (idealmente con drag-and-drop, pero podemos empezar con botones "subir/bajar").
4.  **Permita Modificación Manual:** Que el usuario pueda editar las fechas/horas calculadas para cualquier curso individualmente si es necesario.
5.  **Tenga un botón "Guardar Bloque"** que envíe al backend:
    *   El ID del grupo.
    *   El código de bloque.
    *   El ID del aula seleccionada para el bloque.
    *   El ID del instructor seleccionado para el bloque.
    *   Un array con los cursos, su **orden**, y sus **fechas/horas de inicio y fin finales** (ya sean calculadas o modificadas).

**Revisión del Código PHP:**

*   El método `ordenar()` en `ProgramacionBloqueController` ya recibe el `grupo_id` y `cursos_id`. Está bien para pasar los datos iniciales a la vista.
*   **Falta** el método `storeBloque()` (o similar) que recibirá los datos finales del formulario de ordenación y creará las múltiples entradas en la tabla `programaciones`.
*   **Falta** una API para realizar el cálculo complejo de fechas/horas del bloque, que será llamada por el botón "Calcular Fechas".

**Revisión del Código Blade/Alpine (`ordenar.blade.php`):**

*   El código actual es muy básico y **no tiene la lógica de cálculo ni la interfaz** para ingresar fecha inicio, aula, instructor, ni para mostrar/editar las fechas calculadas por curso.
*   El script Alpine `ordenarBloque` tiene una función `calcularBloque` que intenta hacer el cálculo, pero parece **incompleta y potencialmente incorrecta** (usa `Date` de JS que puede ser complicado con zonas horarias y saltos, y la lógica de asignación de minutos no parece seguir exactamente las reglas).

**Propuesta de Implementación:**

1.  **Backend:**
    *   Crearemos una nueva ruta API (ej: `admin.programaciones.bloque.calcular`) que apunte a un nuevo método en `ProgramacionBloqueController` (ej: `calcularFechasBloqueApi`).
    *   Este método `calcularFechasBloqueApi` recibirá la fecha/hora de inicio, los IDs de los cursos (en orden) y la lista de feriados. Implementará la **lógica de cálculo robusta usando Carbon** (similar a la que hicimos para `calcularFechaFinApi`, pero iterando sobre varios cursos). Devolverá un array JSON con los cursos y sus fechas/horas calculadas.
    *   Implementaremos el método `storeBloque(Request $request)` que recibirá todos los datos finales (incluyendo aula, instructor y el array de cursos con sus fechas/horas), validará (incluyendo verificación de disponibilidad para **todo el rango** del bloque) y creará las entradas en la tabla `programaciones`.
2.  **Frontend (`ordenar.blade.php`):**
    *   Añadiremos inputs para el código de bloque, fecha/hora inicio, selectores para Aula e Instructor.
    *   Mostraremos la lista de cursos seleccionados (pasados desde el controlador) como elementos que se puedan reordenar (empezaremos con botones subir/bajar, luego podríamos añadir drag-and-drop). Cada item mostrará inputs (inicialmente `readonly`) para sus fechas/horas.
    *   El botón "Calcular Fechas" llamará a la nueva API del backend usando `fetch`.
    *   Cuando la API responda, el script Alpine actualizará los campos de fecha/hora de cada curso en la lista y los hará editables.
    *   El botón rounded p-4 bg-gray-50 shadow-sm cursor-grab group" :data-id="curso.id">
                             {{-- Input oculto con el ID del curso (para el backend) --}}
                             <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">

                             {{-- Nombre y Orden --}}
                             <div class="flex justify-between items-center mb-3">
                                 <div>
                                    <span class="inline-flex items-center justify-center w-6 h-6 mr-2 bg-gray-200 text-gray-600 rounded-full text-xs font-bold" x-text="index + 1"></span>
                                    <strong class="text-indigo-800 group-hover:text-indigo-600" x-text="curso.nombre"></strong>
                                 </div>
                                 <span class="text-xs text-gray-500" x-text="`(${curso.duracion_horas}h acad.)`"></span>
                             </div>

                             {{-- Inputs para Fechas y Horas --}}
                             <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                 <div>
                                     <label :for="'fecha_inicio_'+curso.id" class="block font-medium text-gray-600 mb-1">Fecha Inicio</label>
                                     <input type="date" :id="'fecha_inicio_'+curso.id" required
                                            :name="`cursos[${index}][fecha_inicio]`"
                                            x-model="curso.fecha_inicio"
                                            @change="marcarModificado" {{-- Marcar si se cambia manualmente --}}
                                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-1.5 text-xs">
                                 </div>
                                 <div>
                                     <label :for="'hora_inicio_'+curso.id" class="block font-medium text-gray-600 mb-1">Hora Inicio</label>
                                     <input type="time" :id="'hora_inicio_'+curso.id" required
                                            :name="`cursos[${index}][hora_inicio]`"
                                            x-model="curso.hora_inicio"
                                            @change="marcarModificado"
                                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-1.5 text-xs">
                                 </div>
                                 <div>
                                     <label :for="'fecha_fin_'+curso.id" class="block font-medium text-gray-600 mb-1">Fecha Fin</label>
                                     <input type="date" :id="'fecha_fin_'+curso.id" required
                                            :name="`cursos[${index}][fecha_fin]`"
                                            x-model="curso.fecha_fin"
                                             @change="marcarModificado"
                                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-1.5 text-xs">
                                 </div>
                                 <div>
                                     <label :for="'hora_fin_'+curso.id" class="block font-medium text-gray-600 mb-1">Hora Fin</label>
                                     <input type="time" :id="'hora_fin_'+curso.id" required
                                            :name="`cursos[${index}][hora_fin]`"
                                            x-model="curso.hora_fin"
                                             @change="marcarModificado"
                                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-1.5 text-xs">
                                 </div>
                             </div>
                             {{-- Indicador visual si fue modificado manualmente --}}
                             <p x-show="curso.modificado" class="text-xs text-orange-600 mt-1 italic" x-cloak>Fechas/horas modificadas manualmente.</p>

                         </li>
                     </template>
                 </ul>

                 {{-- Botón Guardar --}}
                 <div class="mt-6 pt-6 border-t text-center">
                     <button type="button" @click="submitForm()" :disabled="cursos.length === 0 || !fechasCalculadas"
                             class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                         Guardar Programación del Bloque
                     </button>
                     <p x-show="!fechasCalculadas && cursos.length > 0" class="text-sm text-red-500 mt-2">Debes calcular los horarios antes de guardar.</p>
                 </div>
            </form> {{-- Fin Formulario Principal --}}
        </div>
    </div>

    {{-- Script Alpine para esta vista --}}
    <script>
        function ordenarBloque(config) {
            return {
                cursos: config.cursosIniciales || [], // Array de {id, nombre, duracion_horas, ...}
                feriados: new Set(config.feriados || []), // Usar un Set para búsqueda rápida
                fechaInicioBloque: '',
                bloqueCodigo: '',
                fechasCalculadas: false, // Flag para saber si ya se calcularon
                rutaStoreBloque: config.rutaStoreBloque,

                init() {
                    console.log('Alpine: Ordenar Bloque inicializado con', this.cursos.length, 'cursos.');
                    // Usar $nextTick para asegurar que el DOM (incluido x-ref) esté listo
                    this.$nextTick(() => {
                        console.log('Alpine: $nextTick en init ejecutado.');
                        const sortableList = this.$refs.sortableList;
                        if (sortableList) {
                            if (typeof Sortable === 'undefined') {
                                console.error("Alpine: SortableJS no está definido.");
                                alert("Error: La función de reordenar no está disponible.");
                                return;
                            }
                            console.log("Alpine: Inicializando SortableJS en:", sortableList);
                            try { // Añadir try-catch por si falla la inicialización
                                Sortable.create(sortableList, {
                                    animation: 150,
                                    handle: '.cursor-grab', // Asegúrate que li tiene esta clase
                                    ghostClass: 'bg-blue-100',
                                    chosenClass: "opacity-50",
                                    dragClass: "opacity-50",
                                    onEnd: (evt) => {
                                        console.log('Alpine: SortableJS onEnd - Moviendo item'); // Log para confirmar evento
                                        const [movedItem] = this.cursos.splice(evt.oldIndex, 1);
                                        this.cursos.splice(evt.newIndex, 0, movedItem);
                                        console.log('Alpine: Curso movido, nuevo orden IDs:', this.cursos.map(c => c.id));
                                        this.fechasCalculadas = false;
                                    }
                                });
                                console.log("Alpine: SortableJS inicializado con éxito.");
                            } catch (e) {
                                console.error("Alpine: ¡ERROR al inicializar SortableJS!", e);
                            }
                        } else {
                            console.error("Alpine: Elemento x-ref='sortableList' no encontrado DENTRO de $nextTick.");
                        }
                    }); // Fin $nextTick
                }, // Fin init

                // Marcar curso como modificado manualmente
                marcarModificado(event) {
                    const cursoId = event.target.id.split('_').pop(); // Obtener ID del curso desde el ID del input
                    const cursoIndex = this.cursos.findIndex(c => c.id == cursoId);
                    if (cursoIndex > -1) {
                        this.cursos[cursoIndex].modificado = true;
                         // Cuando se modifica manualmente, ya no confiamos en el cálculo automático
                        this.fechasCalculadas = false;
                        console.log(`Alpine: Curso ID ${cursoId} marcado como modificado.`);
                    }
                },

                // Calcular horarios para todo el bloque
                            // Calcular horarios para todo el bloque usando Date de JS
            calcularHorariosBloque() {
                if (!this.fechaInicioBloque) { alert('Seleccione fecha de inicio.'); return; }
                if (this.cursos.length === 0) { alert('No hay cursos.'); return; }

                console.log('Alpine: Calculando horarios JS...');
                const MINUTOS_HORA_ACADEMICA = 50;
                const feriados = this.feriados; // Ya es un Set

                // Convertir horarios a minutos desde medianoche para facilitar cálculos
                const horarioMananaInicio = 8 * 60 + 30; // 510
                const horarioMananaFin = 12 * 60;          // 720
                const horarioTardeInicio = 13 * 60;         // 780
                const horarioTardeFin = 17 * 60;          // 1020

                // Intentar parsear la fecha de inicio del bloque
                let cursorTiempo; // Usaremos milisegundos para el cursor
                try {
                     // Crear fecha JS. ¡CUIDADO ZONA HORARIA! Asume zona horaria local del navegador.
                     // Forzar inicio a las 08:30
                     cursorTiempo = new Date(`${this.fechaInicioBloque}T08:30:00`).getTime();
                     // Verificar si la fecha inicial es válida
                     if (isNaN(cursorTiempo)) throw new Error('Fecha de inicio inválida');
                } catch (e) {
                     console.error("Error parseando fecha inicio:", e);
                     alert("La fecha de inicio ingresada no es válida.");
                     return;
                }


                this.cursos.forEach((curso, index) => {
                    let minutosPendientes = curso.duracion_horas * MINUTOS_HORA_ACADEMICA;
                    console.log(` - Calculando JS curso ${index + 1}: ${curso.nombre} (${minutosPendientes} min)`);

                    // 1. Ajustar cursor al inicio del día/horario laboral
                    cursorTiempo = this.ajustarInicioCursorJS(cursorTiempo, horarioMananaInicio, feriados);
                    const inicioCursoDate = new Date(cursorTiempo);
                    curso.fecha_inicio = this.formatDateToYMD(inicioCursoDate);
                    curso.hora_inicio = this.formatDateToHM(inicioCursoDate);
                    curso.modificado = false;
                    console.log(`   Cursor JS inicial ajustado a: ${curso.fecha_inicio} ${curso.hora_inicio}`);

                    // 2. Consumir minutos
                    while (minutosPendientes > 0) {
                         cursorTiempo = this.ajustarInicioCursorJS(cursorTiempo, horarioMananaInicio, feriados); // Reajustar por si saltó día
                         let cursorDate = new Date(cursorTiempo);
                         let minutosHoy = 0;
                         let minutosEnDia = cursorDate.getHours() * 60 + cursorDate.getMinutes();

                         // Calcular minutos disponibles en la mañana
                         if (minutosEnDia < horarioMananaFin) {
                             minutosHoy += horarioMananaFin - Math.max(minutosEnDia, horarioMananaInicio);
                         }
                         // Calcular minutos disponibles en la tarde
                         if (minutosEnDia < horarioTardeFin) {
                              // Si está antes de la 1 PM, calcular desde la 1 PM
                             if (minutosEnDia < horarioTardeInicio) {
                                 minutosHoy += horarioTardeFin - horarioTardeInicio;
                             } else { // Si ya está en la tarde, calcular desde la hora actual
                                 minutosHoy += horarioTardeFin - minutosEnDia;
                             }
                         }

                         const minutosAUsar = Math.min(minutosPendientes, minutosHoy);

                         if (minutosAUsar <= 0) { // No quedan minutos hoy, pasar al día siguiente
                             console.log(`   No quedan minutos hábiles hoy (${cursorDate.toISOString().slice(0,10)}), pasando al siguiente.`);
                             cursorTiempo = this.pasarAlSiguienteDiaHabil(cursorTiempo, horarioMananaInicio, feriados);
                             continue; // Volver a empezar el while con el nuevo día
                         }

                         // Avanzar el cursor consumiendo minutos, saltando almuerzo
                          let minutosConsumidosTramo = 0;
                          while (minutosConsumidosTramo < minutosAUsar) {
                              let tempCursorDate = new Date(cursorTiempo);
                              let tempMinutosEnDia = tempCursorDate.getHours() * 60 + tempCursorDate.getMinutes();
                              let minutosHastaFinTramo = 0;

                              // ¿Estamos en la mañana?
                              if (tempMinutosEnDia >= horarioMananaInicio && tempMinutosEnDia < horarioMananaFin) {
                                  minutosHastaFinTramo = horarioMananaFin - tempMinutosEnDia;
                              }
                              // ¿Estamos en la tarde?
                              else if (tempMinutosEnDia >= horarioTardeInicio && tempMinutosEnDia < horarioTardeFin) {
                                  minutosHastaFinTramo = horarioTardeFin - tempMinutosEnDia;
                              }
                               // ¿Estamos en el almuerzo o fuera de horario? -> mover al siguiente tramo/día
                               else {
                                   cursorTiempo = this.moverASiguienteTramoOHabil(cursorTiempo, horarioMananaInicio, horarioTardeInicio, feriados);
                                   // console.log("Movido a siguiente tramo/dia:", new Date(cursorTiempo).toLocaleString());
                                   continue; // Reevaluar en el while principal con el nuevo cursor
                               }


                              let minutosEstePaso = Math.min(minutosAUsar - minutosConsumidosTramo, minutosHastaFinTramo);
                              cursorTiempo += minutosEstePaso * 60000; // Añadir milisegundos
                              minutosConsumidosTramo += minutosEstePaso;
                          }


                         minutosPendientes -= minutosAUsar;
                         console.log(`   + Asignados ${minutosAUsar} min. Restan: ${minutosPendientes}. Cursor JS ahora en: ${new Date(cursorTiempo).toLocaleString()}`);

                         // Si se acabaron los minutos, salir del while
                         if (minutosPendientes <= 0) break;

                         // Si no se acabaron pero se terminó el día, pasar al siguiente día hábil
                         if (new Date(cursorTiempo).getHours() * 60 + new Date(cursorTiempo).getMinutes() >= horarioTardeFin) {
                             console.log(`   Fin del día ${new Date(cursorTiempo).toISOString().slice(0,10)}, quedan ${minutosPendientes} min. Pasando al siguiente día.`);
                             cursorTiempo = this.pasarAlSiguienteDiaHabil(cursorTiempo, horarioMananaInicio, feriados);
                         }

                    } // Fin while minutosPendientes

                    // Asignar fecha/hora de fin
                    const finCursoDate = new Date(cursorTiempo);
                    curso.fecha_fin = this.formatDateToYMD(finCursoDate);
                    curso.hora_fin = this.formatDateToHM(finCursoDate);
                    console.log(`   * Curso ${index + 1} JS finaliza: ${curso.fecha_fin} ${curso.hora_fin}`);

                    // --- REGLA ADICIONAL: Si termina >= 15:00, el siguiente empieza al otro día ---
                     if (finCursoDate.getHours() >= 15 && index < this.cursos.length - 1) {
                         console.log(`   Terminó >= 15:00, siguiente curso inicia mañana.`);
                         cursorTiempo = this.pasarAlSiguienteDiaHabil(cursorTiempo, horarioMananaInicio, feriados);
                     }
                     // --- FIN REGLA ADICIONAL ---

                }); // Fin forEach cursos

                this.fechasCalculadas = true;
                console.log('Alpine: Cálculo JS de horarios completado.');
                // Forzar actualización visual si es necesario (a veces Alpine necesita ayuda)
                this.$nextTick(() => console.log('Alpine: Tick después de cálculo JS'));
            }, // Fin calcularHorariosBloque (JS)

            // Helper JS para ajustar el cursor al inicio del día/horario laboral
            ajustarInicioCursorJS(timestamp, inicioDiaMinutos, feriados) {
                 let cursor = new Date(timestamp);
                 while ([0, 6].includes(cursor.getDay()) || feriados.has(this.formatDateToYMD(cursor))) {
                     cursor.setDate(cursor.getDate() + 1);
                 }
                 // Ajustar a la hora de inicio si es antes
                 let currentMinutos = cursor.getHours() * 60 + cursor.getMinutes();
                 if (currentMinutos < inicioDiaMinutos) {
                      cursor.setHours(Math.floor(inicioDiaMinutos / 60), inicioDiaMinutos % 60, 0, 0);
                 }
                 // Si está en hora de almuerzo, mover a las 13:00
                 if (cursor.getHours() === 12) {
                     cursor.setHours(13, 0, 0, 0);
                 }
                 return cursor.getTime();
             },

             // Helper JS para pasar al siguiente día hábil a las 8:30
             pasarAlSiguienteDiaHabil(timestamp, inicioDiaMinutos, feriados) {
                  let cursor = new Date(timestamp);
                  cursor.setDate(cursor.getDate() + 1); // Mover al día siguiente
                   // Saltar findes/feriados
                  while ([0, 6].includes(cursor.getDay()) || feriados.has(this.formatDateToYMD(cursor))) {
                      cursor.setDate(cursor.getDate() + 1);
                  }
                  // Establecer hora de inicio
                  cursor.setHours(Math.floor(inicioDiaMinutos / 60), inicioDiaMinutos % 60, 0, 0);
                  return cursor.getTime();
              },

              // Helper JS para mover al siguiente tramo o día hábil si está fuera de horario
              moverASiguienteTramoOHabil(timestamp, inicioMananaMin, inicioTardeMin, feriados) {
                    let cursor = new Date(timestamp);
                    let minutosEnDia = cursor.getHours() * 60 + cursor.getMinutes();
                    // Si está antes de las 8:30 o durante el almuerzo
                    if (minutosEnDia < inicioMananaMin || (minutosEnDia >= 12*60 && minutosEnDia < inicioTardeMin)) {
                        cursor.setHours(Math.floor(inicioTardeMin / 60), inicioTardeMin % 60, 0, 0);
                    }
                    // Si está después de las 17:00
                    else {
                         cursor = new Date(this.pasarAlSiguienteDiaHabil(timestamp, inicioMananaMin, feriados));
                    }
                    return cursor.getTime();
              },


             // Helper JS para formatear Fecha YYYY-MM-DD
             formatDateToYMD(date) {
                 const d = date.getDate().toString().padStart(2, '0');
                 const m = (date.getMonth() + 1).toString().padStart(2, '0'); // Month is 0-indexed
                 const y = date.getFullYear();
                 return `${y}-${m}-${d}`;
             },

             // Helper JS para formatear Hora HH:MM
             formatDateToHM(date) {
                 const h = date.getHours().toString().padStart(2, '0');
                 const min = date.getMinutes().toString().padStart(2, '0');
                 return `${h}:${min}`;
             },


                // Envía el formulario (se podría validar aquí también)
                submitForm() {
                    if (!this.fechasCalculadas) {
                         if (!confirm('Los horarios no han sido calculados o fueron modificados. ¿Desea guardar con las fechas/horas actuales?')) {
                             return;
                         }
                    }
                     if (this.cursos.length === 0) {
                         alert('No hay cursos seleccionados para guardar.');
                         return;
                     }
                    console.log('Alpine: Enviando formulario de bloque...');
                    this.$refs.formGuardarBloque.submit();
                }
            };
        }
    </script>
</x-app-layout>
