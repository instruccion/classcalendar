<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            📄 Documentos del Instructor: {{ $instructor->nombre }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto space-y-6">
        <div class="bg-white shadow rounded p-4">
            <h3 class="text-lg font-semibold mb-4">Documentos Actuales</h3>
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Documento</th>
                        <th class="px-4 py-2 text-left">Vence</th>
                        <th class="px-4 py-2 text-left">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($instructor->documentos as $doc)
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
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Formulario de asignación -->
        <div class="bg-white shadow rounded p-4">
            <h3 class="text-lg font-semibold mb-4">Asignar Documento</h3>
            <form method="POST" action="{{ route('admin.instructores.asignarDocumento', $instructor) }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">Documento</label>
                        <select name="documento_id" class="w-full border px-4 py-2 rounded" required>
                            <option value="">Seleccione</option>
                            @foreach ($documentos as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->nombre }}</option>
                            @endforeach
                        </select>
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
