<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Bloque – {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coordinación' }})
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto"
         x-data="ordenarBloque({
             cursosIniciales: {{ Js::from($cursosParaVista) }},
             feriados: {{ Js::from($feriados) }},
             grupoId: {{ $grupo->id }},
             bloqueCodigoOriginal: '{{ $bloque_codigo ?? '_sin_codigo_' }}',
             rutaUpdateBloque: '{{ route('admin.programaciones.bloque.update', ['grupo' => $grupo->id, 'bloque_codigo' => $bloque_codigo ?? '_sin_codigo_']) }}'
         })" x-init="init()">

        <div class="bg-white p-6 rounded shadow-md">
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                     class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded shadow">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <form x-ref="formGuardarBloque" method="POST" :action="rutaUpdateBloque">
                @csrf
                @method('PUT')
                <input type="hidden" name="grupo_id" :value="grupoId">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Código del Bloque</label>
                        <input type="text" name="bloque_codigo" x-model="bloqueCodigo"
                               class="w-full border px-3 py-2 rounded" placeholder="Ej: BLOQ-01">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Inicio Primer Curso</label>
                        <input type="date" x-model="fechaInicioBloque"
                               class="w-full border px-3 py-2 rounded">
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="calcularHorariosBloque"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow w-full">
                            Recalcular Fechas
                        </button>
                    </div>
                </div>

                <ul class="space-y-4" x-ref="sortableList">
                    <template x-for="(curso, index) in cursos" :key="curso.programacion_id">
                        <li class="border rounded p-4 bg-gray-50 shadow-sm">
                            <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">
                            <input type="hidden" :name="`cursos[${index}][programacion_id]`" :value="curso.programacion_id">
                            <input type="hidden" :name="`cursos[${index}][modificado]`" :value="curso.modificado ? '1' : '0'">

                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <strong x-text="curso.nombre" class="text-blue-700"></strong>
                                    <span class="text-xs text-gray-500 ml-2" x-text="`(${curso.duracion_horas}h)`"></span>
                                </div>
                                <button type="button" @click="cursos.splice(index, 1)"
                                        class="text-red-600 text-xs hover:underline">
                                    Eliminar
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="text-sm">Fecha Inicio</label>
                                    <input type="date" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][fecha_inicio]`" x-model="curso.fecha_inicio">
                                </div>
                                <div>
                                    <label class="text-sm">Hora Inicio</label>
                                    <input type="time" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][hora_inicio]`" x-model="curso.hora_inicio">
                                </div>
                                <div>
                                    <label class="text-sm">Fecha Fin</label>
                                    <input type="date" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][fecha_fin]`" x-model="curso.fecha_fin">
                                </div>
                                <div>
                                    <label class="text-sm">Hora Fin</label>
                                    <input type="time" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][hora_fin]`" x-model="curso.hora_fin">
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="text-sm font-medium text-gray-700">Pertenece al Bloque:</label>
                                <span class="text-sm font-semibold text-indigo-600 underline ml-1" x-text="bloqueCodigo || '—' "></span>
                            </div>
                        </li>
                    </template>
                </ul>

                <div class="mt-6 text-center">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
