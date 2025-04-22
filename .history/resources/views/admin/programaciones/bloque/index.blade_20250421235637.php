<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar Cursos por Bloque
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto"
         x-data="bloqueForm({
            grupos: {{ Js::from($grupos) }},
            rutaCursos: '{{ route('admin.api.programaciones.cursosPorGrupo', ['grupo' => ':grupoId']) }}',
            rutaOrdenar: '{{ route('admin.programaciones.bloque.ordenar') }}' {{-- Asumiendo que tienes esta ruta para el siguiente paso --}}
         })">

        <div class="bg-white p-6 rounded shadow-md">
            <div class="flex justify-between items-center mb-6 pb-3 border-b">
                <h1 class="text-2xl font-bold">Programar por Bloque: Selección de Cursos</h1>
                <a href="{{ route('admin.programaciones.create') }}" class="text-blue-600 hover:underline text-sm">
                    ← Volver a Programación Individual
                </a>
            </div>

            {{-- Filtros --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="grupo_id" class="block font-semibold mb-1 text-sm text-gray-700">1. Seleccione Grupo <span class="text-red-500">*</span></label>
                    <select x-model="selectedGroupId" @change="loadCursos()" id="grupo_id"
                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2">
                        <option value="">-- Seleccione --</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coord.' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tipo" class="block font-semibold mb-1 text-sm text-gray-700">2. Filtrar por Tipo de Curso (Opcional)</label>
                    <select x-model="selectedTipo" @change="loadCursos()" id="tipo" :disabled="!selectedGroupId"
                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-2 disabled:bg-gray-100">
                        <option value="">Todos</option>
                        {{-- Asumiendo que estos son tus tipos de curso --}}
                        <option value="Inicial">Inicial</option>
                        <option value="Periódico">Periódico</option>
                        <option value="General">General</option>
                        <option value="Específico">Específico</option>
                        <option value="OJT">On The Job Traininig</option>

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
                            <div class="flex justify-between items-center p-2 border rounded bg-white shadow-sm text-sm">
                                <span x-text="curso.nombre"></span>
                                <button type="button" @click="pasarUno(curso)" title="Añadir"
                                        class="text-green-600 hover:text-green-800 p-1 rounded hover:bg-green-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 11.414V14a1 1 0 102 0v-2.586l.293.293a1 1 0 001.414-1.414z" clip-rule="evenodd" /></svg> {{-- Icono mover derecha --}}
                                </button>
                            </div>
                        </template>
                    </div>
                     <button type="button" @click="pasarTodos()" :disabled="availableCourses.length === 0 || isLoadingCursos"
                            class="mt-2 w-full text-center text-sm text-blue-600 hover:underline disabled:text-gray-400 disabled:cursor-not-allowed">
                         Añadir todos »
                    </button>
                </div>

                <!-- Botones separadores (solo visual en pantallas grandes) -->
                 <div class="md:col-span-1 flex-col items-center justify-center pt-10 hidden md:flex">
                     {{-- Podríamos poner iconos aquí si quisiéramos, pero las acciones están en cada item --}}
                 </div>

                <!-- Cursos seleccionados -->
                <div class="md:col-span-5">
                    <h2 class="font-semibold text-gray-800 mb-2">Cursos Seleccionados (Bloque)</h2>
                     <div class="border rounded-md p-3 h-80 overflow-y-auto bg-blue-50 space-y-2">
                        <p x-show="selectedCoursesData.length === 0" class="text-gray-500 text-center py-4">Añada cursos desde la izquierda.</p>
                        <template x-for="curso in selectedCoursesData" :key="curso.id">
                             <div class="flex justify-between items-center p-2 border rounded bg-white shadow-sm text-sm">
                                 <button type="button" @click="regresarUno(curso)" title="Quitar"
                                        class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-100 mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd" /></svg> {{-- Icono mover izquierda --}}
                                </button>
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
                <form method="GET" :action="ordenarUrl" x-ref="formOrdenar">
                    <input type="hidden" name="grupo_id" :value="selectedGroupId">
                    {{-- Llenar los IDs seleccionados dinámicamente --}}
                    <template x-for="cursoId in selectedCoursesIds" :key="cursoId">
                        <input type="hidden" name="cursos_id[]" :value="cursoId">
                    </template>

                    <button type="submit" :disabled="selectedCoursesIds.length === 0"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow-md disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Continuar y Ordenar Cursos Seleccionados
                    </button>
                     <p x-show="selectedCoursesIds.length === 0" class="text-sm text-red-500 mt-2">Debe seleccionar al menos un curso.</p>
                </form>
            </div>

        </div>
    </div>

    <script>
    function bloqueForm(config) {
        return {
            grupos: config.grupos || [],
            selectedGroupId: '',
            selectedTipo: '',
            availableCourses: [], // Cursos del grupo que NO están seleccionados
            selectedCoursesData: [], // Array de OBJETOS curso seleccionados (para display)
            selectedCoursesIds: [], // Array de IDs seleccionados (para lógica y form)
            isLoadingCursos: false,
            rutaCursos: config.rutaCursos,
            ordenarUrl: config.rutaOrdenar, // URL para el siguiente paso

            // Propiedad computada para obtener el nombre del grupo seleccionado
            get selectedGroupName() {
                 const grupo = this.grupos.find(g => g.id == this.selectedGroupId);
                 return grupo ? grupo.nombre : '';
            },

            // Carga cursos filtrando por grupo y tipo, excluyendo los ya seleccionados
            loadCursos() {
                this.availableCourses = []; // Limpiar disponibles
                if (!this.selectedGroupId) return;

                this.isLoadingCursos = true;
                const url = new URL(this.rutaCursos.replace(':grupoId', this.selectedGroupId), window.location.origin);
                if (this.selectedTipo) {
                    url.searchParams.append('tipo', this.selectedTipo); // Añadir tipo si está seleccionado
                }

                console.log('Fetching cursos desde:', url.toString());
                fetch(url)
                    .then(res => res.ok ? res.json() : Promise.reject('Error API'))
                    .then(data => {
                        // Filtrar los que NO están ya en selectedCoursesIds
                        this.availableCourses = data.filter(curso => !this.selectedCoursesIds.includes(curso.id.toString()));
                        console.log('Cursos disponibles cargados:', this.availableCourses.length);
                    })
                    .catch(error => {
                        console.error("Error cargando cursos:", error);
                        this.availableCourses = []; // Limpiar en caso de error
                        alert('Error al cargar cursos para este grupo.');
                    })
                    .finally(() => this.isLoadingCursos = false);
            },

            // Mover un curso de Disponible a Seleccionado
            pasarUno(curso) {
                this.selectedCoursesData.push(curso); // Añadir objeto a la lista seleccionada
                this.selectedCoursesIds.push(curso.id.toString()); // Añadir ID a la lista de IDs
                this.availableCourses = this.availableCourses.filter(c => c.id !== curso.id); // Quitar de disponibles
            },

            // Mover todos los cursos de Disponible a Seleccionado
            pasarTodos() {
                this.availableCourses.forEach(curso => {
                     this.selectedCoursesData.push(curso);
                     this.selectedCoursesIds.push(curso.id.toString());
                });
                this.availableCourses = []; // Vaciar disponibles
            },

            // Mover un curso de Seleccionado a Disponible
            regresarUno(curso) {
                this.availableCourses.push(curso); // Devolver a la lista disponible
                this.selectedCoursesData = this.selectedCoursesData.filter(c => c.id !== curso.id); // Quitar de seleccionados (objetos)
                this.selectedCoursesIds = this.selectedCoursesIds.filter(id => id !== curso.id.toString()); // Quitar de seleccionados (IDs)
                // Reordenar disponibles alfabéticamente (opcional)
                this.availableCourses.sort((a, b) => a.nombre.localeCompare(b.nombre));
            },

            // Mover todos los cursos de Seleccionado a Disponible
            regresarTodos() {
                this.selectedCoursesData.forEach(curso => {
                     this.availableCourses.push(curso);
                });
                this.selectedCoursesData = []; // Vaciar seleccionados (objetos)
                this.selectedCoursesIds = []; // Vaciar seleccionados (IDs)
                // Reordenar disponibles alfabéticamente (opcional)
                this.availableCourses.sort((a, b) => a.nombre.localeCompare(b.nombre));
            }
        };
    }
    </script>
</x-app-layout>
