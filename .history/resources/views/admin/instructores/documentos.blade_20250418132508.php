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

    {{-- Modal para editar documento --}}
<dialog id="modalEditarDocumento" class="w-full max-w-md p-0 rounded shadow-lg backdrop:bg-black/30">
    <div class="bg-white p-6">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h3 class="text-lg font-bold">Editar Documento</h3>
            <button onclick="document.getElementById('modalEditarDocumento').close()" class="text-gray-600 hover:text-black text-xl">&times;</button>
        </div>

        {{-- ⚠️ El formulario no tiene `action` aún, será inyectado por JS --}}
        <form id="formEditarDocumento" onsubmit="return enviarDocumento(event)">

            @csrf
            @method('PUT')
            <input type="hidden" name="pivot_id" id="pivot_id_editar">

            <div class="mb-4">
                <label class="block font-semibold mb-1">Documento</label>
                <input type="text" id="nombre_doc_editar" disabled class="w-full border px-4 py-2 rounded bg-gray-100">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Fecha de Vencimiento</label>
                <input type="date" name="fecha_vencimiento" id="fecha_vencimiento_editar" class="w-full border px-4 py-2 rounded">
            </div>
            <div class="text-center">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</dialog>

{{-- Script correcto y seguro para editar --}}
<script>
    function abrirModalEditar(pivot_id, nombre, fecha) {
        const form = document.getElementById('formEditarDocumento');

        // ✅ CONSTRUCCIÓN SEGURA Y EXPLÍCITA
        const base = window.location.origin + "/cursoslaser/public/index.php/admin/instructores/documentos/";
        const fullAction = base + pivot_id;
        form.setAttribute('action', fullAction);

        // 🧪 LOG para depuración visual inmediata
        alert("🧭 Action generado: " + fullAction);

        // ✅ Cargar valores en campos del formulario
        document.getElementById('pivot_id_editar').value = pivot_id;
        document.getElementById('nombre_doc_editar').value = nombre;
        document.getElementById('fecha_vencimiento_editar').value = fecha ?? '';

        // ✅ Mostrar modal
        document.getElementById('modalEditarDocumento').showModal();
    }
</script>

<script>
    function abrirModalEditar(pivot_id, nombre, fecha) {
        document.getElementById('pivot_id_editar').value = pivot_id;
        document.getElementById('nombre_doc_editar').value = nombre;
        document.getElementById('fecha_vencimiento_editar').value = fecha ?? '';
        document.getElementById('modalEditarDocumento').showModal();
    }

    async function enviarDocumento(e) {
        e.preventDefault();

        const pivot_id = document.getElementById('pivot_id_editar').value;
        const fecha = document.getElementById('fecha_vencimiento_editar').value;

        const url = `${window.location.origin}/cursoslaser/public/index.php/admin/instructores/documentos/${pivot_id}`;

        const token = document.querySelector('input[name="_token"]').value;

        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('_token', token);
        formData.append('fecha_vencimiento', fecha);

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            alert('✅ Documento actualizado correctamente.');
            location.reload();
        } else {
            alert('❌ Error al actualizar. Revisa consola o respuesta de Laravel.');
            console.log(await response.text());
        }

        return false;
    }
</script>




</x-app-layout>
