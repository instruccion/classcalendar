<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">Gestión de Aulas</h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">🏫 Aulas Registradas</h1>
            <button onclick="abrirModalAula()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Nueva Aula
            </button>
        </div>

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Lugar</th>
                        <th class="px-4 py-2 text-left">Capacidad</th>
                        <th class="px-4 py-2 text-left">Videobeam</th>
                        <th class="px-4 py-2 text-left">Computadora</th>
                        <th class="px-4 py-2 text-left">Activa</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($aulas as $aula)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $aula->nombre }}</td>
                            <td class="px-4 py-2">{{ $aula->lugar }}</td>
                            <td class="px-4 py-2 text-center">{{ $aula->capacidad }}</td>
                            <td class="px-4 py-2 text-center">{{ $aula->videobeam ? '✅' : '❌' }}</td>
                            <td class="px-4 py-2 text-center">{{ $aula->computadora ? '✅' : '❌' }}</td>
                            <td class="px-4 py-2 text-center">{{ $aula->activa ? '✅' : '❌' }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                <button type="button"
                                    class="text-blue-600 hover:underline"
                                    onclick='editarAula(@json($aula))'>Editar</button>
                                <form action="{{ route('aulas.destroy', $aula) }}" method="POST"
                                      onsubmit="return confirm('¿Deseas eliminar esta aula?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-500 py-4">No hay aulas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <dialog id="modalAula" class="rounded-lg w-full max-w-2xl overflow-hidden backdrop:bg-black/30">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h2 class="text-xl font-bold" id="modalTitulo">Registrar Nueva Aula</h2>
                <button onclick="document.getElementById('modalAula').close()" class="text-xl text-gray-600 hover:text-black">&times;</button>
            </div>

            <form method="POST" action="{{ route('aulas.store') }}" id="formAula" class="grid grid-cols-12 gap-4">
                @csrf
                <input type="hidden" name="id" id="aulaId">

                <div class="col-span-12 md:col-span-6">
                    <label class="block font-semibold mb-1">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="w-full border rounded px-4 py-2" required maxlength="100">
                </div>

                <div class="col-span-12 md:col-span-4">
                    <label class="block font-semibold mb-1">Lugar</label>
                    <input type="text" name="lugar" id="lugar" class="w-full border rounded px-4 py-2" maxlength="30">
                </div>

                <div class="col-span-12 md:col-span-2">
                    <label class="block font-semibold mb-1">Capacidad</label>
                    <input type="number" name="capacidad" id="capacidad" min="1" class="w-full border rounded px-4 py-2 text-center">
                </div>

                <div class="col-span-6">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="videobeam" id="videobeam" value="1">
                        Videobeam
                    </label>
                </div>

                <div class="col-span-6">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="computadora" id="computadora" value="1">
                        Computadora
                    </label>
                </div>

                <div class="col-span-12">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="activa" id="activa" value="1" checked>
                        Aula activa
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
            document.getElementById('formAula').action = "{{ route('aulas.store') }}";
            document.getElementById('aulaId').value = '';
            document.getElementById('nombre').value = '';
            document.getElementById('lugar').value = '';
            document.getElementById('capacidad').value = '';
            document.getElementById('videobeam').checked = false;
            document.getElementById('computadora').checked = false;
            document.getElementById('activa').checked = true;
            document.getElementById('modalAula').showModal();
        }

        function editarAula(aula) {
            document.getElementById('modalTitulo').textContent = 'Editar Aula';
            document.getElementById('formAula').action = `/admin/aulas/${aula.id}`;
            document.getElementById('formAula').insertAdjacentHTML('beforeend', '<input type="hidden" name="_method" value="PUT">');
            document.getElementById('aulaId').value = aula.id;
            document.getElementById('nombre').value = aula.nombre;
            document.getElementById('lugar').value = aula.lugar;
            document.getElementById('capacidad').value = aula.capacidad;
            document.getElementById('videobeam').checked = aula.videobeam == 1;
            document.getElementById('computadora').checked = aula.computadora == 1;
            document.getElementById('activa').checked = aula.activa == 1;
            document.getElementById('modalAula').showModal();
        }
    </script>
</x-app-layout>
