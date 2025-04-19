{{-- resources\views\admin\instructores\documentos.blade.php --}}
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
                        <th class="px-4 py-2 text-left">Estado y Acciones</th> {{-- Columna combinada para claridad --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse($instructor->documentos as $doc)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $doc->nombre }}</td>
                            <td class="px-4 py-2">
                                {{-- Formateo seguro de fecha --}}
                                @if($doc->pivot->fecha_vencimiento)
                                    {{ \Carbon\Carbon::parse($doc->pivot->fecha_vencimiento)->format('d/m/Y') }}
                                @else
                                    No vence
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if ($doc->pivot->fecha_vencimiento && \Carbon\Carbon::parse($doc->pivot->fecha_vencimiento)->isPast())
                                    <span class="text-red-600 font-bold">Vencido</span>
                                @else
                                    <span class="text-green-600">Vigente</span>
                                @endif
                                {{-- Botón Editar: Asegúrate de pasar la fecha en formato YYYY-MM-DD para el input date --}}
                                <button onclick="abrirModalEditar('{{ $doc->pivot->id }}', '{{ $doc->nombre }}', '{{ $doc->pivot->fecha_vencimiento ? \Carbon\Carbon::parse($doc->pivot->fecha_vencimiento)->format('Y-m-d') : '' }}')"
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
                        <label for="nombre_nuevo" class="block font-semibold mb-1">Nombre del Documento</label> {{-- Añadido ID y for --}}
                        <input type="text" id="nombre_nuevo" name="nombre" required
                               class="w-full border px-4 py-2 rounded"
                               placeholder="Ej: Componente Docente">
                    </div>
                    <div>
                        <label for="fecha_vencimiento_nuevo" class="block font-semibold mb-1">Fecha de Vencimiento</label> {{-- Añadido ID y for --}}
                        <input type="date" id="fecha_vencimiento_nuevo" name="fecha_vencimiento" class="w-full border px-4 py-2 rounded">
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
                <button onclick="document.getElementById('modalEditarDocumento').close()" class="text-gray-600 hover:text-black text-xl">×</button>
            </div>

            {{-- Formulario CORREGIDO: Se quitó onsubmit y se añadió method="POST" --}}
            {{-- La acción será inyectada por JS --}}
            <form id="formEditarDocumento" method="POST"> {{-- <--- CORRECCIÓN AQUÍ --}}

                @csrf
                @method('PUT')
                {{-- Se eliminó el input oculto pivot_id ya que el ID va en la URL --}}

                <div class="mb-4">
                    <label class="block font-semibold mb-1">Documento</label>
                    {{-- El nombre no se edita, solo se muestra --}}
                    <input type="text" id="nombre_doc_editar" disabled class="w-full border px-4 py-2 rounded bg-gray-100">
                </div>
                <div class="mb-4">
                    <label for="fecha_vencimiento_editar" class="block font-semibold mb-1">Fecha de Vencimiento</label> {{-- Añadido for --}}
                    <input type="date" name="fecha_vencimiento" id="fecha_vencimiento_editar" class="w-full border px-4 py-2 rounded">
                </div>
                <div class="text-center mt-4"> {{-- Añadido mt-4 para espacio --}}
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Guardar Cambios
                    </button>
                     <button type="button" onclick="document.getElementById('modalEditarDocumento').close()" class="ml-2 bg-gray-200 text-sm px-4 py-2 rounded hover:bg-gray-300">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Script CORREGIDO para editar --}}

    <script>
        function abrirModalEditar(data) { // <--- Acepta un solo argumento 'data'
            const form = document.getElementById('formEditarDocumento');
            console.log("--- Abrir Modal ---");
            console.log("Datos recibidos:", data); // Log del objeto completo

            // Extraer valores del objeto 'data'
            const pivot_id = data.pivot_id;
            const nombre = data.nombre;
            const fechaYmd = data.fecha_vencimiento; // Ya viene en YYYY-MM-DD o null

            console.log("Pivot ID extraído:", pivot_id);
            console.log("Nombre extraído:", nombre);
            console.log("Fecha YMD extraída:", fechaYmd);

            // Construcción de la URL para el action del formulario
            const baseUrlString = @json(rtrim(url('admin/instructores/documentos'), '/'));
            console.log("Base URL desde Blade:", baseUrlString);

            // Verificar que pivot_id sea válido (ahora debería ser un número o null/undefined si falla)
            if (!pivot_id || typeof pivot_id !== 'number') { // Más estricto: debe ser un número
                console.error("¡ERROR: pivot_id es inválido o no es un número!", pivot_id);
                alert("Error: No se pudo obtener un ID numérico válido para el documento a editar.");
                return; // Detener ejecución
            }

            const fullAction = `${baseUrlString}/${pivot_id}`;
            console.log("URL de acción calculada:", fullAction);

            try {
                form.setAttribute('action', fullAction);
                console.log("Atributo 'action' establecido en el form:", form.getAttribute('action'));
            } catch (error) {
                console.error("Error al establecer el atributo 'action':", error);
                alert("Error al configurar el formulario de edición.");
                return;
            }

            // Cargar valores en campos
            try {
                document.getElementById('nombre_doc_editar').value = nombre ?? ''; // Usar ?? '' por si acaso
                document.getElementById('fecha_vencimiento_editar').value = fechaYmd ?? ''; // Ya viene formateada o null
                console.log("Valores cargados en el formulario.");
            } catch (error) {
                console.error("Error al cargar valores en el formulario:", error);
                alert("Error al cargar los datos del documento en el formulario.");
                return;
            }

            // Mostrar modal
            try {
                document.getElementById('modalEditarDocumento').showModal();
                console.log("Modal mostrado.");
            } catch (error) {
                console.error("Error al mostrar el modal:", error);
                alert("Error al mostrar la ventana de edición.");
            }
            console.log("--- Fin Abrir Modal ---");
        }
    </script>
</x-app-layout>
