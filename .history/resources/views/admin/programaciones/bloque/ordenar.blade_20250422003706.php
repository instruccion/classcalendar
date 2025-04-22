<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Ordenar Cursos del Bloque – {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coordinación' }})
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto"
         x-data="ordenarBloque({ cursosIniciales: {{ Js::from($cursosSeleccionados) }} })"
         x-init="init()">

        <div class="bg-white p-6 rounded shadow-md">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Paso 2: Reordenar Cursos y Confirmar Detalles</h1>
                <p class="text-sm text-gray-600">Puedes arrastrar los cursos para cambiar el orden o editar sus detalles individualmente.</p>
            </div>

            <form method="POST" action="{{ route('admin.programaciones.bloque.store') }}" @submit.prevent="submitForm" x-ref="form">
                @csrf

                <input type="hidden" name="grupo_id" value="{{ $grupo->id }}">

                <ul class="space-y-4" x-ref="sortableList">
                    <template x-for="(curso, index) in cursos" :key="curso.id">
                        <li class="border rounded p-4 bg-gray-50 shadow-sm cursor-move">
                            <div class="flex justify-between items-center mb-2">
                                <strong class="text-blue-800" x-text="curso.nombre"></strong>
                                <span class="text-xs text-gray-500">Orden: <span x-text="index + 1"></span></span>
                            </div>

                            <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium">Fecha Inicio</label>
                                    <input type="date" class="w-full rounded border px-3 py-2" :name="`cursos[${index}][fecha_inicio]`" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium">Hora Inicio</label>
                                    <input type="time" class="w-full rounded border px-3 py-2" :name="`cursos[${index}][hora_inicio]`" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium">Instructor</label>
                                    <input type="text" class="w-full rounded border px-3 py-2" :name="`cursos[${index}][instructor]`">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium">Aula</label>
                                    <input type="text" class="w-full rounded border px-3 py-2" :name="`cursos[${index}][aula]`">
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>

                <div class="mt-6 text-center">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow">
                        Guardar Programación en Bloque
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SortableJS para arrastrar cursos --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
    function ordenarBloque(config) {
        return {
            cursos: config.cursosIniciales || [],

            init() {
                new Sortable(this.$refs.sortableList, {
                    animation: 150,
                    handle: '.cursor-move',
                    onEnd: () => {
                        // Reordenar array cursos según el DOM
                        const items = Array.from(this.$refs.sortableList.children);
                        const nuevos = items.map(li => {
                            const nombre = li.querySelector('strong')?.textContent.trim();
                            return this.cursos.find(c => c.nombre === nombre);
                        }).filter(Boolean);
                        this.cursos = nuevos;
                    }
                });
            },

            submitForm() {
                this.$refs.form.submit();
            }
        };
    }
    </script>
</x-app-layout>
