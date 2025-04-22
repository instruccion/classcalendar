<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Ordenar Cursos del Bloque – {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coord.' }})
        </h2>
    </x-slot>

    {{-- Script para SortableJS (Drag and Drop) - Cargar antes de Alpine --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    {{-- No se necesita Carbon JS --}}

    {{-- ============================================= --}}
    {{--  SCRIPT ALPINE DEFINIDO ANTES DE USARSE      --}}
    {{-- ============================================= --}}
    <script>
        function ordenarBloque(config) {
            return {
                // --- Estado ---
                // Los cursos vienen con id, nombre, duracion_horas
                cursos: config.cursosIniciales || [],
                grupoId: config.grupoId,
                rutaStoreBloque: config.rutaStoreBloque,
                // Datos que el usuario debe ingresar para el bloque
                fechaInicioBloque: '',
                horaInicioBloque: '08:30', // Hora inicio por defecto
                bloqueCodigo: '',
                aulaId: '',         // ID del aula seleccionada
                instructorId: '',   // ID del instructor seleccionado

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
                                    handle: '.handle', // Clase para el icono de agarre
                                    ghostClass: 'bg-blue-100 opacity-50',
                                    onEnd: (evt) => {
                                        // Actualizar el orden del array 'cursos' en Alpine
                                        const [movedItem] = this.cursos.splice(evt.oldIndex, 1);
                                        this.cursos.splice(evt.newIndex, 0, movedItem);
                                        console.log('Alpine: Curso movido, nuevo orden IDs:', this.cursos.map(c => c.id));
                                        // No es necesario recalcular aquí, se hará en backend al guardar
                                    }
                                });
                            } catch(e) { console.error("Error inicializando Sortable:", e); }
                        } else {
                            console.error("Alpine: SortableJS o x-ref='sortableList' no encontrado.");
                        }
                    });
                }, // Fin init

                // Envía el formulario al backend para guardar y calcular
                submitForm() {
                    // Validación Frontend Simple
                     if (!this.fechaInicioBloque) { alert('Seleccione Fecha Inicio del Bloque.'); return; }
                     if (!this.horaInicioBloque) { alert('Seleccione Hora Inicio del Bloque.'); return; }
                     if (!this.aulaId) { alert('Seleccione un Aula para el bloque.'); return; }
                     if (!this.instructorId) { alert('Seleccione un Instructor para el bloque.'); return; }
                     if (this.cursos.length === 0) { alert('No hay cursos en el bloque.'); return; }

                    console.log('Alpine: Enviando formulario de bloque para cálculo y guardado...');

                    // Los inputs hidden dentro del template se encargarán de enviar los IDs
                    // en el orden correcto. Añadimos los otros campos al formulario.
                    const form = this.$refs.formGuardarBloque;

                    // Crear inputs ocultos adicionales para enviar datos del bloque si no existen
                    if (!form.querySelector('input[name="fecha_inicio_bloque"]')) {
                        let inputFecha = document.createElement('input');
                        inputFecha.type = 'hidden'; inputFecha.name = 'fecha_inicio_bloque';
                        inputFecha.value = this.fechaInicioBloque; form.appendChild(inputFecha);
                    } else { form.querySelector('input[name="fecha_inicio_bloque"]').value = this.fechaInicioBloque;}

                     if (!form.querySelector('input[name="hora_inicio_bloque"]')) {
                        let inputHora = document.createElement('input');
                        inputHora.type = 'hidden'; inputHora.name = 'hora_inicio_bloque';
                        inputHora.value = this.horaInicioBloque; form.appendChild(inputHora);
                    } else { form.querySelector('input[name="hora_inicio_bloque"]').value = this.horaInicioBloque;}

                    if (!form.querySelector('input[name="aula_id"]')) {
                        let inputAula = document.createElement('input');
                        inputAula.type = 'hidden'; inputAula.name = 'aula_id';
                        inputAula.value = this.aulaId; form.appendChild(inputAula);
                    } else { form.querySelector('input[name="aula_id"]').value = this.aulaId;}

                     if (!form.querySelector('input[name="instructor_id"]')) {
                        let inputInstructor = document.createElement('input');
                        inputInstructor.type = 'hidden'; inputInstructor.name = 'instructor_id';
                        inputInstructor.value = this.instructorId; form.appendChild(inputInstructor);
                    } else { form.querySelector('input[name="instructor_id"]').value = this.instructorId;}

                     if (!form.querySelector('input[name="bloque_codigo"]')) {
                        let inputCodigo = document.createElement('input');
                        inputCodigo.type = 'hidden'; inputCodigo.name = 'bloque_codigo';
                        inputCodigo.value = this.bloqueCodigo; form.appendChild(inputCodigo);
                    } else { form.querySelector('input[name="bloque_codigo"]').value = this.bloqueCodigo;}


                    form.submit(); // Enviar formulario
                }
            };
        }
    </script>
    {{-- ============================================= --}}
    {{-- FIN SCRIPT ALPINE --}}
    {{-- ============================================= --}}


    {{-- DIV QUE USA EL SCRIPT ALPINE (x-data) --}}
    <div class="py-6 max-w-4xl mx-auto" {{-- Reducido max-w para mejor centrado --}}
         x-data="ordenarBloque({
             cursosIniciales: {{ Js::from($cursosSeleccionados->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre, 'duracion_horas' => $c->duracion_horas])) }},
             feriados: {{ Js::from($feriados ?? []) }}, // Feriados ya no se usan en JS
             grupoId: {{ $grupo->id }},
             rutaStoreBloque: '{{ route('admin.programaciones.bloque.store') }}' // Ruta para guardar
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

                {{-- Sección Superior: Código Bloque, Fecha/Hora Inicio, Aula, Instructor --}}
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
                            {{-- Cargar Aulas Activas (Necesitan pasarse desde el controlador 'ordenar') --}}
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
                            {{-- Cargar Instructores Activos (Necesitan pasarse desde el controlador 'ordenar') --}}
                             @foreach(\App\Models\Instructor::where('activo', true)->orderBy('nombre')->get() as $instructor)
                                <option value="{{ $instructor->id }}">{{ $instructor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                 {{-- Lista Reordenable de Cursos --}}
                 <h2 class="text-lg font-semibold mb-2 text-gray-800">Cursos en el Bloque (Arrastra el icono <span class="text-xl">≡</span> para reordenar)</h2>
                 <p x-show="cursos.length === 0" class="text-gray-500">No hay cursos seleccionados.</p>

                 <ul x-ref="sortableList" class="space-y-2 mb-6 min-h-[5rem] border rounded-md p-4 bg-gray-50">
                     <template x-for="(curso, index) in cursos" :key="curso.id">
                         <li class="border rounded p-3 bg-white shadow-sm group flex items-center" :data-id="curso.id">
                             {{-- Handle para arrastrar --}}
                             <span class="handle cursor-grab text-gray-400 hover:text-gray-600 mr-3" title="Arrastrar para reordenar">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                             </span>
                             {{-- Input oculto con el ID del curso (para el backend) --}}
                             <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">
                             {{-- Nombre y Duración --}}
                             <div class="flex-grow">
                                <span class="font-medium text-indigo-800" x-text="curso.nombre"></span>
                                <span class="text-xs text-gray-500 ml-2" x-text="`(${curso.duracion_horas}h acad.)`"></span>
                             </div>
                             {{-- Eliminamos los inputs de fecha/hora individuales --}}
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
                 </div>
            </form>

        </div>
    </div>

</x-app-layout>
