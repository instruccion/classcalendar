<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar Cursos por Bloque
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto"
         x-data="bloqueForm({
            grupos: {{ Js::from($grupos) }},
            rutaCursos: '{{ url('admin/programaciones/bloque/cursos') }}',
            rutaOrdenar: '{{ route('admin.programaciones.bloque.ordenar') }}'
         })">

        <div class="bg-white p-6 rounded shadow-md">
            <div class="flex justify-between items-center mb-6 pb-3 border-b">
                <h1 class="text-2xl font-bold">Paso 1: Selección de Cursos por Bloque</h1>
                <a href="{{ route('admin.programaciones.index') }}" class="text-blue-600 hover:underline text-sm">
                    ← Volver a la Lista de Programaciones
                </a>
            </div>

            {{-- Filtros --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block font-semibold text-sm text-gray-700 mb-1">Grupo <span class="text-red-500">*</span></label>
                    <select x-model="selectedGroupId" @change="loadCursos()"
                            class="w-full border-gray-300 rounded shadow-sm py-2">
                        <option value="">-- Seleccione --</option>
                        <template x-for="grupo in grupos" :key="grupo.id">
                            <option :value="grupo.id" x-text="`${grupo.nombre} (${grupo.coordinacion?.nombre || 'Sin Coord.'})`"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-sm text-gray-700 mb-1">Tipo de Curso</label>
                    <select x-model="selectedTipo" @change="loadCursos()" :disabled="!selectedGroupId"
                            class="w-full border-gray-300 rounded shadow-sm py-2 disabled:bg-gray-100">
                        <option value="">Todos</option>
                        <option value="Inicial">Inicial</option>
                        <option value="Recurrente">Periódico</option>
                        <option value="General">General</option>
                        <option value="Específico">Específico</option>
                        <option value="OJT">On The Job Training</option>
                    </select>
                </div>
            </div>

            {{-- Listado y selección --}}
            <div class="grid grid-cols-1 md:grid-cols-11 gap-4 items-start" x-show="selectedGroupId" x-transition>
                <div class="md:col-span-5">
                    <h2 class="font-semibold text-gray-800 mb-2">Cursos Disponibles</h2>
                    <div class="border p-3 rounded bg-gray-50 h-80 overflow-y-auto space-y-2">
                        <p x-show="isLoadingCursos" class="text-gray-500 text-center">Cargando cursos...</p>
                        <template x-for="curso in availableCourses" :key="curso.id">
                            <div class="flex justify-between items-center bg-white p-2 border rounded shadow-sm text-sm">
                                <span x-text="curso.nombre"></span>
                                <button type="button" @click="pasarUno(curso)" class="text-green-600 hover:text-green-800">
                                    ➕
                                </button>
                            </div>
                        </template>
                        <p x-show="!isLoadingCursos && availableCourses.length === 0" class="text-center text-gray-500 text-sm">
                            No hay cursos disponibles.
                        </p>
                    </div>
                    <button type="button" @click="pasarTodos()" class="mt-2 w-full text-sm text-blue-600 hover:underline">
                        Añadir todos »
                    </button>
                </div>

                <div class="md:col-span-1 flex items-center justify-center text-center hidden md:flex">
                    ➡️⬅️
                </div>

                <div class="md:col-span-5">
                    <h2 class="font-semibold text-gray-800 mb-2">Cursos Seleccionados</h2>
                    <form method="GET" :action="ordenarUrl" x-ref="formOrdenar">
                        <input type="hidden" name="grupo_id" :value="selectedGroupId">
                        <template x-for="cursoId in selectedCoursesIds" :key="cursoId">
                            <input type="hidden" name="cursos_id[]" :value="cursoId">
                        </template>

                        <div class="border p-3 rounded bg-blue-50 h-80 overflow-y-auto space-y-2">
                            <template x-for="curso in selectedCoursesData" :key="curso.id">
                                <div class="flex justify-between items-center bg-white p-2 border rounded shadow-sm text-sm">
                                    <button type="button" @click="regresarUno(curso)" class="text-red-600 hover:text-red-800">
                                        ❌
                                    </button>
                                    <span x-text="curso.nombre" class="flex-grow text-left ml-2"></span>
                                </div>
                            </template>
                            <p x-show="selectedCoursesData.length === 0" class="text-center text-gray-500 text-sm">
                                No ha seleccionado cursos aún.
                            </p>
                        </div>

                        <button type="button" @click="regresarTodos()" class="mt-2 w-full text-sm text-red-600 hover:underline">
                            « Quitar todos
                        </button>

                        <div class="mt-6 text-center">
                            <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow-md"
                                    :disabled="selectedCoursesIds.length === 0">
                                Continuar con Orden
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function bloqueForm(config) {
        return {
            grupos: config.grupos || [],
            selectedGroupId: '',
            selectedTipo: '',
            availableCourses: [],
            selectedCoursesData: [],
            selectedCoursesIds: [],
            isLoadingCursos: false,
            rutaCursos: config.rutaCursos,
            ordenarUrl: config.rutaOrdenar,

            loadCursos() {
                this.availableCourses = [];
                if (!this.selectedGroupId) return;

                this.isLoadingCursos = true;

                const url = new URL(this.rutaCursos, window.location.origin);
                url.searchParams.append('grupo_id', this.selectedGroupId);
                if (this.selectedTipo) url.searchParams.append('tipo', this.selectedTipo);

                fetch(url)
                    .then(res => res.ok ? res.json() : Promise.reject('Error API'))
                    .then(data => {
                        this.availableCourses = data.filter(c => !this.selectedCoursesIds.includes(c.id.toString()));
                    })
                    .catch(error => {
                        console.error("Error cargando cursos:", error);
                        alert('Error al cargar cursos.');
                    })
                    .finally(() => this.isLoadingCursos = false);
            },


            pasarUno(curso) {
                this.selectedCoursesData.push(curso);
                this.selectedCoursesIds.push(curso.id.toString());
                this.availableCourses = this.availableCourses.filter(c => c.id !== curso.id);
            },

            pasarTodos() {
                this.availableCourses.forEach(curso => {
                    this.selectedCoursesData.push(curso);
                    this.selectedCoursesIds.push(curso.id.toString());
                });
                this.availableCourses = [];
            },

            regresarUno(curso) {
                this.availableCourses.push(curso);
                this.selectedCoursesData = this.selectedCoursesData.filter(c => c.id !== curso.id);
                this.selectedCoursesIds = this.selectedCoursesIds.filter(id => id !== curso.id.toString());
                this.availableCourses.sort((a, b) => a.nombre.localeCompare(b.nombre));
            },

            regresarTodos() {
                this.availableCourses.push(...this.selectedCoursesData);
                this.selectedCoursesData = [];
                this.selectedCoursesIds = [];
                this.availableCourses.sort((a, b) => a.nombre.localeCompare(b.nombre));
            }
        }
    }
    </script>
</x-app-layout>
