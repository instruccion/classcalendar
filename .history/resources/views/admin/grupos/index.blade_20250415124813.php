<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Grupos</h2>
    </x-slot>

    <div class="py-4 max-w-6xl mx-auto">
        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Grupos</h1>
            <button onclick="document.getElementById('modalNuevoGrupo').showModal()"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Nuevo Grupo
            </button>
        </div>



        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Coordinación</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grupos as $grupo)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $grupo->nombre }}</td>
                            <td class="px-4 py-2">{{ $grupo->coordinacion->nombre ?? '—' }}</td>
                            <td class="px-4 py-2 flex gap-3">
                                <button onclick="abrirModalEditar({{ $grupo->id }}, '{{ $grupo->nombre }}', '{{ $grupo->coordinacion_id }}')"
                                        class="text-blue-600 hover:underline">Editar</button>
                                <form method="POST" action="{{ route('admin.grupos.destroy', $grupo) }}" onsubmit="return confirm('¿Eliminar grupo?')">

                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-4 text-gray-500">No hay grupos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Nuevo Grupo --}}
    <dialog id="modalNuevoGrupo" class="rounded-lg w-full max-w-xl p-0 overflow-hidden shadow-xl backdrop:bg-black/30">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h2 class="text-xl font-bold">Registrar Grupo</h2>
                <button onclick="document.getElementById('modalNuevoGrupo').close()"
                        class="text-gray-600 hover:text-black text-xl">&times;</button>
            </div>
            <form action="{{ route('admin.grupos.store') }}" method="POST" class="grid grid-cols-1 gap-4">

                @csrf
                <div>
                    <label class="block font-semibold mb-1">Nombre</label>
                    <input type="text" name="nombre" required class="w-full border px-4 py-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Coordinación</label>
                    <select name="coordinacion_id" required class="w-full border px-4 py-2 rounded">
                        <option value="">Seleccione una</option>
                        @foreach ($coordinaciones as $coor)
                            <option value="{{ $coor->id }}">{{ $coor->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-center mt-2">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Modal Editar Grupo --}}
    <dialog id="modalEditarGrupo" class="rounded-lg w-full max-w-xl p-0 overflow-hidden shadow-xl backdrop:bg-black/30">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h2 class="text-xl font-bold">Editar Grupo</h2>
                <button onclick="document.getElementById('modalEditarGrupo').close()" class="text-gray-600 hover:text-black text-xl">&times;</button>
            </div>
            <form id="formEditarGrupo" method="POST" action="{{ route('admin.grupos.update', $grupo) }}" class="grid grid-cols-1 gap-4">
                @csrf @method('PUT')
                <input type="hidden" name="id" id="grupo_edit_id">
                <div>
                    <label class="block font-semibold mb-1">Nombre</label>
                    <input type="text" name="nombre" id="grupo_edit_nombre" required class="w-full border px-4 py-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Coordinación</label>
                    <select name="coordinacion_id" id="grupo_edit_coordinacion" required class="w-full border px-4 py-2 rounded">
                        @foreach ($coordinaciones as $coor)
                            <option value="{{ $coor->id }}">{{ $coor->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-center mt-2">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar</button>
                </div>
            </form>
        </div>
    </dialog>


    <script>
        function abrirModalEditar(id, nombre, coordinacion_id) {
            const modal = document.getElementById('modalEditarGrupo');
            document.getElementById('grupo_edit_id').value = id;
            document.getElementById('grupo_edit_nombre').value = nombre;
            document.getElementById('grupo_edit_coordinacion').value = coordinacion_id;
            const form = document.getElementById('formEditarGrupo');
            form.action = `/admin/grupos/${id}`;
            modal.showModal();
        }


        document.getElementById('coordinacion')?.addEventListener('change', function () {
            const coordinacionId = this.value;

            fetch(`/admin/grupos-por-coordinacion?coordinacion_id=${coordinacionId}`)
                .then(response => response.json())
                .then(data => {
                    const grupoSelect = document.getElementById('grupo');
                    grupoSelect.innerHTML = '<option value="">Todos los grupos</option>';
                    data.grupos.forEach(grupo => {
                        const option = document.createElement('option');
                        option.value = grupo.id;
                        option.textContent = grupo.nombre;
                        grupoSelect.appendChild(option);
                    });
                });
        });


    </script>



</x-app-layout>
