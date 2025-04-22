<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar Cursos por Bloque (Paso 1: Selección)
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto"
         x-data="bloqueForm({
            grupos: {{ Js::from($grupos) }},
            {{-- Ajustar nombre de ruta para la API de cursos --}}
            rutaCursos: '{{ route('admin.programaciones.bloque.getCursosApi') }}',
            {{-- Ajustar nombre de ruta para el siguiente paso (ordenar) --}}
            rutaOrdenar: '{{ route('admin.programaciones.bloque.ordenar') }}'
         })">

        <div class="bg-white p-6 rounded shadow-md">
            <div class="flex justify-between items-center mb-6 pb-3 border-b">
                <h1 class="text-2xl font-bold">Selección de Cursos para el Bloque</h1>
                 {{-- Enlace para volver a programación individual --}}
                <a href="{{ route('admin.programaciones.create') }}" class="text-blue-600 hover:underline text-sm">
                    ← Programación Individual
                </a>
            </div>

            {{-- Filtros --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="grupo_id" class="block font-semibold mb-1 text-sm text-gray-700">1. Seleccione Grupo <span class="text-red-500">*</span></label>
                    <select x-model="selectedGroupId" @change="loadCursos()" id="grupo_id"
                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2">
                        <option value="">-- Seleccione --</option>
                        {{-- Iterar sobre los grupos pasados desde el controlador --}}
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coord.' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tipo" class="block font-semibold mb-1 text-sm text-gray-700">2. Filtrar por Tipo (Opcional)</label>
                    <select x-model="selectedTipo" @change="loadCursos()" id="tipo" :disabled="!selectedGroupId"
                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2 disabled:bg-gray-100">
                        <option value="">Todos</option>
                        {{-- Añadir los tipos de curso que tengas --}}
                        <option value="Inicial">Inicial</option>
                        <option value="Periódico">Periódico</option>
                        <option value="General">General</option>
                        <option value="Específico">Específico</option>
                        <option value="Recurrente">Recurrente</option>
                        <option value="Puntual">Puntual</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>

            {{-- Listas de Selección --}}
            <div class="grid grid-cols-1 md:grid-cols-11 gap-4 items-start" x-show="selectedGroupId" x-transition>
                <!-- Cursos disponibles -->
                <div class="md:col-span-5">
                    <h2 class="font-semibold text-gray-800 mb-2">Cursos Disponibles para "<span x-text="selectedGroupName" class="font-bold"></span>"</h2>
                    <div class="border rounded-md p-3 h-80 overflow-y-auto bg-gray-50 space-y-2">
                        <p x-show="isLoadingCursos" class="text-gray-500 text-center py-4">Cargando...</p>
                        <p x-show="!isLoadingCursos && availableCourses.length === 0 && selectedGroupId" class="text-gray-500 text-center py-4">No hay cursos disponibles (o ya están seleccionados).</p>
                        <template x-for="curso in availableCourses" :key="curso.id">
                            <div class="flex justify-between items-center p-2 border rounded bg-white shadow-sm text-sm cursor-pointer hover:bg-gray-100" @click="pasarUno(curso)">
                                <span x-text="curso.nombre"></span>
                                <span class="text-green-500 ml-2">→</span>
                            </div>
                        </template>
                    </div>
                     <button type="button" @click="pasarTodos()" :disabled="availableCourses.length === 0 || isLoadingCursos"
                            class="mt-2 w-full text-center text-sm text-blue-600 hover:underline disabled:text-gray-400 disabled:cursor-not-allowed">
                         Añadir todos »
                    </button>
                </div>

                <!-- Divisor (opcional, podrías quitarlo) -->
                 <div class="md:col-span-1 flex-col items-center justify-center pt-10 hidden md:flex">
                      <span class="text-gray-400 text-2xl">↔</span>
                 </div>

                <!-- Cursos seleccionados -->
                <div class="md:col-span-5">
                    <h2 class="font-semibold text-gray-800 mb-2">Cursos Seleccionados (Bloque)</h2>
                     <div class="border rounded-md p-3 h-80 overflow-y-auto bg-blue-50 space-y-2">
                        <p x-show="selectedCoursesData.length === 0" class="text-gray-500 text-center py-4">Añada cursos desde la izquierda.</p>
                        <template x-for="curso in selectedCoursesData" :key="curso.id">
                             <div class="flex justify-between items-center p-2 border rounded bg-white shadow-sm text-sm cursor-pointer hover:bg-gray-100" @click="regresarUno(curso)">
                                 <span class="text-red-500 mr-2">←</span>
                                <span x-text="curso.nombre" class="flex-grow text-left"></span>
                            </div>
                        </template>
                    </div>
                     <button type="button" @click="regresarTodos()" :disabled="selectedCoursesData.length === 0"
                            class="mt-2 w-full text-center text-sm text-red-600 hover:underline disabled:text-gray-400 disabled:cursor-not-allowed">
                         « Quitar todos
                    </button>
                </div>
            </div>

             {{-- Formulario Oculto y Botón Continuar --}}
            <div class="mt-8 pt-4 border-t text-center" x-show="selectedGroupId" x-transition>
                {{-- El action apunta a la ruta para ordenar --}}
                <form method="GET" :action="rutaOrdenar" x-ref="formOrdenar">
                    <input type="hidden" name="grupo_id" :value="selectedGroupId">
                    {{-- Los IDs se añadirán aquí dinámicamente --}}
                    <template x-for="cursoId in selectedCoursesIds" :key="cursoId">
                        <input type="hidden" name="cursos_id[]" :value="cursoId">
                    </template>

                    <button type="submit" :disabled="selectedCoursesIds.length === 0"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Continuar y Ordenar Cursos (<span x-text="selectedCoursesIds.length"></span>)
                    </button>
                     <p x-show="selectedCoursesIds.length === 0" class="text-sm text-red-500 mt-2">Debe seleccionar al menos un curso.</p>
                </form>
            </div>

        </div>
    </div>

    {{-- Script Alpine para esta vista --}}
    <script>
    function bloqueForm(config) {
        return {
            grupos: config.grupos || [],
            selectedGroupId: '',
            selectedTipo: '',
            availableCourses: [],
            selectedCoursesData: [], // Array de {id, nombre}
            selectedCoursesIds: [], // Array de solo IDs
            isLoadingCursos: false,
            rutaCursos: config.rutaCursos,
            rutaOrdenar: config.rutaOrdenar,

            // Propiedad computada para obtener el nombre del grupo
            get selectedGroupName() {
                 const grupo = this.grupos.find(g => g.id == this.selectedGroupId);
                 return grupo ? grupo.nombre : '';
            },

            // Carga cursos para el grupo/tipo seleccionado
            loadCursos() {
                this.availableCourses = [];
                this.selectedCoursesData = []; // Limpiar selección al cambiar grupo/tipo
                this.selectedCoursesIds = [];
                if (!this.selectedGroupId) return;

                this.isLoadingCursos = true;
                const url = new URL(this.rutaCursos.replace(':grupoId', this.selectedGroupId), window.location.origin);
                if (this.selectedTipo) {
                    url.searchParams.append('tipo', this.selectedTipo);
                }

                fetch(url)
                    .then(res => res.ok ? res.json() : Promise.reject('Error API'))
                    .then(data => {
                        this.availableCourses = data.filter(curso => !this.selectedCoursesIds.includes(curso.id.toString()));
                    })
                    .catch(error => { console.error("Error cargando cursos:", error); alert('Error al cargar cursos.'); })
                    .finally(() => this.isLoadingCursos = false);
            },

            // Mover un curso de Disponible a Seleccionado
            pasarUno(curso) {
                if (!this.selectedCoursesIds.includes(curso.id.toString())) {
                    this.selectedCoursesData.push({ id: curso.id, nombre: curso.nombre });
                    this.selectedCoursesIds.push(curso.id.toString());
                    this.availableCourses = this.availableCourses.filter(c => c.id !== curso.id);
                }
            },

            // Mover todos los cursos de Disponible a Seleccionado
            pasarTodos() {
                this.availableCourses.forEach(curso => {
                    if (!this.selectedCoursesIds.includes(curso.id.toString())) {
                         this.selectedCoursesData.push({ id: curso.id, nombre: curso.nombre });
                         this.selectedCoursesIds.push(curso.id.toString());
                    }
                });
                this.availableCourses = [];
            },

            // Mover un curso de Seleccionado a Disponible
            regresarUno(curso) {
                if (!this.availableCourses.some(c => c.id === curso.id)) { // Evitar duplicados
                    this.availableCourses.push({ id: curso.id, nombre: curso.nombre });
                    this.availableCourses.sort((a, b) => a.nombre.localeCompare(b.nombre)); // Reordenar
                }
                this.selectedCoursesData = this.selectedCoursesData.filter(c => c.id !== curso.id);
                this.selectedCoursesIds = this.selectedCoursesIds.filter(id => id !== curso.id.toString());
            },

            // Mover todos los cursos de Seleccionado a Disponible
            regresarTodos() {
                this.selectedCoursesData.forEach(curso => {
                     if (!this.availableCourses.some(c => c.id === curso.id)) {
                         this.availableCourses.push({ id: curso.id, nombre: curso.nombre });
                     }
                });
                this.selectedCoursesData = [];
                this.selectedCoursesIds = [];
                this.availableCourses.sort((a, b) => a.nombre.localeCompare(b.nombre)); // Reordenar
            }
        };
    }
    </script>
</x-app-layout>
