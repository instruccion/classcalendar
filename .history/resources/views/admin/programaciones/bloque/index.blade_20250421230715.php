@extends('layouts.app')

@section('content')

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar por Bloque
        </h2>
    </x-slot>
<div class="py-6 max-w-6xl mx-auto" x-data="bloqueForm()">
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Programar por Bloque</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="font-bold text-lg mb-2">Cursos Disponibles</h2>
                <select multiple x-ref="disponibles" class="w-full h-64 border rounded bg-white"></select>
            </div>
            <div>
                <h2 class="font-bold text-lg mb-2">Cursos Seleccionados</h2>
                <form method="GET" :action="ordenarUrl">
                    <input type="hidden" name="grupo_id" :value="selectedGroupId">
                    <input type="hidden" name="cursos_id[]" :value="curso" x-for="curso in seleccionados">

                    <select multiple x-ref="seleccionados" class="w-full h-64 border rounded bg-white mb-4"></select>

                    <div class="flex gap-2">
                        <button type="button" @click="agregarCursos" class="bg-blue-500 text-white px-4 py-2 rounded">&rarr;</button>
                        <button type="button" @click="quitarCursos" class="bg-red-500 text-white px-4 py-2 rounded">&larr;</button>
                    </div>

                    <button type="submit" class="mt-4 bg-green-600 text-white px-6 py-2 rounded">Continuar con Orden</button>
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

        agregarCursos() {
            const seleccionadosEl = this.$refs.disponibles;
            const destino = this.$refs.seleccionados;
            Array.from(seleccionadosEl.selectedOptions).forEach(opt => {
                if (!this.seleccionados.includes(opt.value)) {
                    this.seleccionados.push(opt.value);
                    const nueva = opt.cloneNode(true);
                    destino.appendChild(nueva);
                    opt.remove();
                }
            });
        },

        quitarCursos() {
            const seleccionadosEl = this.$refs.seleccionados;
            const origen = this.$refs.disponibles;
            Array.from(seleccionadosEl.selectedOptions).forEach(opt => {
                this.seleccionados = this.seleccionados.filter(id => id !== opt.value);
                const nueva = opt.cloneNode(true);
                origen.appendChild(nueva);
                opt.remove();
            });
        }
    };
}
</script>
@endsection
