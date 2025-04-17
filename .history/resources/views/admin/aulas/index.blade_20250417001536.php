<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            🏫 Gestión de Aulas
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto">
        <!-- Botón -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Aulas Registradas</h1>
            <button onclick="abrirModalAula()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                ➕ Nueva Aula
            </button>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto bg-white p-4 rounded shadow">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2">Nombre</th>
                        <th class="px-4 py-2">Lugar</th>
                        <th class="px-4 py-2">Capacidad</th>
                        <th class="px-4 py-2">Videobeam</th>
                        <th class="px-4 py-2">Computadora</th>
                        <th class="px-4 py-2">Activa</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aulas as $aula)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $aula->nombre }}</td>
                            <td class="px-4 py-2">{{ $aula->lugar }}</td>
                            <td class="px-4 py-2">{{ $aula->capacidad }}</td>
                            <td class="px-4 py-2">{{ $aula->videobeam ? '✅' : '❌' }}</td>
                            <td class="px-4 py-2">{{ $aula->computadora ? '✅' : '❌' }}</td>
                            <td class="px-4 py-2">{{ $aula->activa ? '✅' : '❌' }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                <button onclick='editarAula(@json($aula))' class="text-blue-600 hover:underline">Editar</button>
                                <form action="{{ route('admin.aulas.destroy', $aula) }}" method="POST" onsubmit="return confirm('¿Eliminar aula?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-gray-500">No hay aulas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <dialog id="modalAula" class="w-full max-w-3xl p-0 rounded-lg shadow-lg backdrop:bg-black/30">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h2 class="text-xl font-bold" id="modalTitulo">Registrar Nueva Aula</h2>
                <button onclick="cerrarModalAula()" class="text-gray-600 hover:text-black text-xl">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.aulas.store') }}" id="formAula" class="grid grid-cols-12 gap-4">
                @csrf
                <input type="hidden" name="id" id="aula_id">

                <div class="col-span-12 md:col-span-6">
                    <label class="block font-semibold mb-1">Nombre del Aula</label>
                    <input type="text" name="nombre" id="nombre" required maxlength="100" class="w-full border px-4 py-2 rounded">
                </div>

                <div class="col-span-12 md:col-span-4">
                    <label class="block font-semibold mb-1">Lugar</label>
                    <input type="text" name="lugar" id="lugar" maxlength="100" class="w-full border px-4 py-2 rounded">
                </div>

                <div class="col-span-12 md:col-span-2">
                    <label class="block font-semibold mb-1">Capacidad</label>
                    <input type="number" name="capacidad" id="capacidad" min="1" class="w-full border px-4 py-2 rounded text-center">
                </div>

                <div class="col-span-12 flex flex-col gap-2 pl-1">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="videobeam" id="videobeam" value="1"> Videobeam
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="computadora" id="computadora" value="1"> Computadora
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="activa" id="activa" value="1"> Activa
                    </label>
                </div>

                <div class="col-span-12 text-center mt-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Guardar Aula
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        function abrirModalAula() {
            document.getElementById('modalTitulo').textContent = 'Registrar Nueva Aula';
            document.getElementById('formAula').reset();
            document.getElementById('aula_id').value = '';
            document.getElementById('modalAula').showModal();
        }

        function cerrarModalAula() {
            document.getElementById('modalAula').close();
        }

        function editarAula(aula) {
            document.getElementById('modalTitulo').textContent = 'Editar Aula';
            document.getElementById('formAula').action = `/aulas/${aula.id}`;
            document.getElementById('aula_id').value = aula.id;
            document.getElementById('nombre').value = aula.nombre;
            document.getElementById('lugar').value = aula.lugar;
            document.getElementById('capacidad').value = aula.capacidad;
            document.getElementById('videobeam').checked = aula.videobeam == 1;
            document.getElementById('computadora').checked = aula.computadora == 1;
            document.getElementById('activa').checked = aula.activa == 1;

            // Método spoofing para PUT
            const form = document.getElementById('formAula');
            if (!document.getElementById('_method')) {
                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PUT';
                method.id = '_method';
                form.appendChild(method);
            }

            document.getElementById('modalAula').showModal();
        }
    </script>

    @if (session('toast'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Livewire?.emit?.('toast', {
                    type: '{{ session('toast.type') }}',
                    message: '{{ session('toast.message') }}'
                });
            });
        </script>
    @endif

</x-app-layout>
