<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            📄 Documentos del Instructor: {{ $instructor->nombre }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto space-y-6">
        {{-- Botón volver --}}
        <div>
            <a href="{{ route('admin.instructores.index') }}"
               class="inline-block bg-gray-200 text-sm px-4 py-2 rounded hover:bg-gray-300">
               ← Volver a Instructores
            </a>
        </div>

        {{-- Tabla de documentos actuales --}}
        <div class="bg-white shadow rounded p-4">
            <h3 class="text-lg font-semibold mb-4">Documentos Asignados</h3>
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Documento</th>
                        <th class="px-4 py-2 text-left">Vence</th>
                        <th class="px-4 py-2 text-left">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($instructor->documentos as $doc)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $doc->nombre }}</td>
                            <td class="px-4 py-2">
                                {{ $doc->pivot->fecha_vencimiento ? \Carbon\Carbon::parse($doc->pivot->fecha_vencimiento)->format('d/m/Y') : 'No vence' }}
                            </td>
                            <td class="px-4 py-2">
                                @if ($doc->pivot->fecha_vencimiento && \Carbon\Carbon::parse($doc->pivot->fecha_vencimiento)->isPast())
                                    <span class="text-red-600 font-bold">Vencido</span>
                                @else
                                    <span class="text-green-600">Vigente</span>
                                @endif
                                <button onclick="abrirModalEditar('{{ $doc->pivot->id }}', '{{ $doc->nombre }}', '{{ $doc->pivot->fecha_vencimiento }}')"
                                        class="ml-2 text-blue-600 underline text-sm">Editar</button>
                            </td>

                            <td class="px-4 py-2">
                                @if ($doc->pivot->fecha_vencimiento && \Carbon\Carbon::parse($doc->pivot->fecha_vencimiento)->isPast())
                                    <span class="text-red-600 font-bold">Vencido</span>
                                @else
                                    <span class="text-green-600">Vigente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-gray-500 py-3">Sin documentos asignados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Formulario para asignar nuevo documento --}}
        <div class="bg-white shadow rounded p-4">
            <h3 class="text-lg font-semibold mb-4">Asignar Documento Nuevo</h3>
            <form method="POST" action="{{ route('admin.instructores.asignarDocumentoManual', $instructor) }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Nombre del Documento</label>
                        <input type="text" name="nombre" required
                               class="w-full border px-4 py-2 rounded"
                               placeholder="Ej: Componente Docente">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" class="w-full border px-4 py-2 rounded">
                        <small class="text-gray-500">Dejar vacío si no vence.</small>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Asignar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
