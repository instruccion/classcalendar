<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Grupos</h2>
    </x-slot>

    <div class="py-4 max-w-6xl mx-auto">
        {{-- Toasts flotantes --}}
        @if (session('success'))
            <script>
                window.addEventListener('DOMContentLoaded', () => {
                    toast('success', '{{ session('success') }}');
                });
            </script>
        @endif

        @if ($errors->any())
            <script>
                window.addEventListener('DOMContentLoaded', () => {
                    toast('error', 'Corrige los errores del formulario.');
                });
            </script>
        @endif

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Grupos</h1>
            <button onclick="document.getElementById('modalNuevoGrupo').showModal()"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Nuevo Grupo
            </button>
        </div>

        {{-- Filtro por coordinación (solo para administradores) --}}
        @if (auth()->user()->rol === 'administrador' && is_null(auth()->user()->coordinacion_id))
            <form method="GET" action="{{ route('admin.grupos.index') }}" class="mb-4 max-w-sm">
                <label for="coordinacion_id" class="block font-semibold mb-1">Filtrar por Coordinación:</label>
                <select name="coordinacion_id" id="coordinacion_id" onchange="this.form.submit()" class="w-full border px-4 py-2 rounded">
                    <option value="">Todas las coordinaciones</option>
                    @foreach ($coordinaciones as $coor)
                        <option value="{{ $coor->id }}" {{ request('coordinacion_id') == $coor->id ? 'selected' : '' }}>
                            {{ $coor->nombre }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif


        {{-- Tabla de grupos --}}
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
                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                        class="w-full border px-4 py-2 rounded @error('nombre') border-red-500 @enderror" required>
                    @error('nombre')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if (auth()->user()->rol === 'administrador')
                    <div>
                        <label class="block font-semibold mb-1">Coordinación</label>
                        <select name="coordinacion_id"
                                class="w-full border px-4 py-2 rounded @error('coordinacion_id') border-red-500 @enderror" required>
                            <option value="">Seleccione una</option>
                            @foreach ($coordinaciones as $coor)
                                <option value="{{ $coor->id }}" @selected(old('coordinacion_id') == $coor->id)>{{ $coor->nombre }}</option>
                            @endforeach
                        </select>
                        @error('coordinacion_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <input type="hidden" name="coordinacion_id" value="{{ auth()->user()->coordinacion_id }}">
                @endif

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
            <form id="formEditarGrupo" method="POST" class="grid grid-cols-1 gap-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="grupo_edit_id">
                <div>
                    <label class="block font-semibold mb-1">Nombre</label>
                    <input type="text" name="nombre" id="grupo_edit_nombre" required class="w-full border px-4 py-2 rounded">
                </div>

                @if (auth()->user()->rol === 'administrador')
                    <div>
                        <label class="block font-semibold mb-1">Coordinación</label>
                        <select name="coordinacion_id" id="grupo_edit_coordinacion" required class="w-full border px-4 py-2 rounded">
                            @foreach ($coordinaciones as $coor)
                                <option value="{{ $coor->id }}">{{ $coor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="coordinacion_id" id="grupo_edit_coordinacion" value="{{ auth()->user()->coordinacion_id }}">
                @endif

                <div class="text-center mt-2">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Scripts --}}
    <script>
        function abrirModalEditar(id, nombre, coordinacion_id) {
            const modal = document.getElementById('modalEditarGrupo');
            document.getElementById('grupo_edit_id').value = id;
            document.getElementById('grupo_edit_nombre').value = nombre;
            document.getElementById('grupo_edit_coordinacion').value = coordinacion_id;
            document.getElementById('formEditarGrupo').action = `{{ url('admin/grupos') }}/${id}`;
            modal.showModal();
        }

        @if ($errors->any() && old('nombre'))
            document.getElementById('modalNuevoGrupo')?.showModal();
        @endif

        function toast(type, message) {
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            const toast = document.createElement('div');
            toast.className = `fixed top-5 right-5 text-white px-4 py-2 rounded shadow-lg z-50 ${colors[type]}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
    </script>
</x-app-layout>
