<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar por Bloque
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto" x-data="bloqueForm()">
        <div class="bg-white p-6 rounded shadow">
            <h1 class="text-2xl font-bold mb-6">Programar por Bloque</h1>

            <div class="grid grid-cols-1 gap-4 mb-6">
                <div>
                    <label for="grupo_id" class="block font-semibold mb-1">Grupo</label>
                    <select x-model="selectedGroupId" @change="loadCursos()" id="grupo_id"
                            class="w-full border px-4 py-2 rounded">
                        <option value="">Seleccione un grupo...</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin coordinación' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tipo" class="block font-semibold mb-1">Tipo de Curso</label>
                    <select x-model="selectedTipo" @change="loadCursos()" id="tipo"
                            class="w-full border px-4 py-2 rounded">
                        <option value="">Todos</option>
                        <option value="Presencial">Presencial</option>
                        <option value="Virtual">Virtual</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-6 items-center">
                <!-- Cursos disponibles -->
                <div>
                    <h2 class="font-bold text-lg mb-2">Cursos Disponibles</h2>
                    <select multiple x-ref="disponibles" class="w-full h-64 border rounded bg-white"></select>
                </div>

                <!-- Botones en el medio -->
                <div class="flex flex-col items-center gap-2">
                    <button type="button" @click="pasarUno" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xl">➡️</button>
                    <button type="button" @click="pasarTodos" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xl">⏩</button>
                    <button type="button" @click="regresarUno" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xl">⬅️</button>
                    <button type="button" @click="regresarTodos" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xl">⏪</button>
                </div>

                <!-- Cursos seleccionados -->
                <div>
                    <h2 class="font-bold text-lg mb-2">Cursos Seleccionados</h2>
                    <form method="GET" :action="ordenarUrl">
                        <input type="hidden" name="grupo_id" :value="selectedGroupId">
                        <template x-for="curso in seleccionados" :key="curso">
                            <input type="hidden" name="cursos_id[]" :value="curso">
                        </template>

                        <select multiple x-ref="seleccionados" class="w-full h-64 border rounded bg-white mb-4"></select>

                        <div class="text-center">
                            <button type="submit" class="mt-2 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                                Continuar con Orden
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function bloqueForm() {
        return {
            selectedGroupId: '',
            selectedTipo: '',
            seleccionados: [],
            ordenarUrl: '{{ route('admin.programaciones.bloque.ordenar') }}',

            loadCursos() {
                const grupoId = this.selectedGroupId;
                const tipo = this.selectedTipo;
                const url = new URL("{{ route('admin.programaciones.bloque.cursos') }}", window.location.origin);
                url.searchParams.append('grupo_id', grupoId);
                if (tipo) url.searchParams.append('tipo', tipo);

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        const disponibles = this.$refs.disponibles;
                        disponibles.innerHTML = '';
                        data.forEach(curso => {
                            if (!this.seleccionados.includes(curso.id.toString())) {
                                const option = document.createElement('option');
                                option.value = curso.id;
                                option.textContent = curso.nombre;
                                disponibles.appendChild(option);
                            }
                        });
                    });
            },

            pasarUno() {
                const origen = this.$refs.disponibles;
                const destino = this.$refs.seleccionados;
                Array.from(origen.selectedOptions).forEach(opt => {
                    if (!this.seleccionados.includes(opt.value)) {
                        this.seleccionados.push(opt.value);
                        const nueva = opt.cloneNode(true);
                        destino.appendChild(nueva);
                        opt.remove();
                    }
                });
            },

            pasarTodos() {
                const origen = this.$refs.disponibles;
                const destino = this.$refs.seleccionados;
                Array.from(origen.options).forEach(opt => {
                    if (!this.seleccionados.includes(opt.value)) {
                        this.seleccionados.push(opt.value);
                        const nueva = opt.cloneNode(true);
                        destino.appendChild(nueva);
                        opt.remove();
                    }
                });
            },

            regresarUno() {
                const origen = this.$refs.seleccionados;
                const destino = this.$refs.disponibles;
                Array.from(origen.selectedOptions).forEach(opt => {
                    this.seleccionados = this.seleccionados.filter(id => id !== opt.value);
                    const nueva = opt.cloneNode(true);
                    destino.appendChild(nueva);
                    opt.remove();
                });
            },

            regresarTodos() {
                const origen = this.$refs.seleccionados;
                const destino = this.$refs.disponibles;
                Array.from(origen.options).forEach(opt => {
                    this.seleccionados = this.seleccionados.filter(id => id !== opt.value);
                    const nueva = opt.cloneNode(true);
                    destino.appendChild(nueva);
                    opt.remove();
                });
            }
        };
    }
    </script>
</x-app-layout>
