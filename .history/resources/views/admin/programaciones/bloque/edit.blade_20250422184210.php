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
         })"
         x-init="init()">

        <div class="bg-white p-6 rounded shadow-md">
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                     class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded shadow">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <form x-ref="formGuardarBloque" method="POST" :action="rutaUpdateBloque">
                @csrf
                @method('PUT')
                <input type="hidden" name="grupo_id" :value="grupoId">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 items-end">
                    <div>
                        <label for="bloque_codigo" class="block text-sm font-semibold text-gray-700">Código del Bloque</label>
                        <input type="text" id="bloque_codigo" name="bloque_codigo" x-model="bloqueCodigo"
                               class="w-full border px-3 py-2 rounded shadow-sm" placeholder="Ej: BLOQ-01">
                    </div>

                    <div>
                        <label for="fecha_inicio_bloque" class="block text-sm font-semibold text-gray-700">Fecha Inicio Primer Curso</label>
                        <input type="date" id="fecha_inicio_bloque" x-model="fechaInicioBloque"
                               class="w-full border px-3 py-2 rounded shadow-sm">
                    </div>

                    <div>
                        <button type="button" @click="calcularHorariosBloque"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 w-full rounded shadow-md">
                            Recalcular Fechas
                        </button>
                    </div>
                </div>
