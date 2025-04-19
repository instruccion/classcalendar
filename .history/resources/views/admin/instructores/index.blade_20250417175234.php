<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">🧑‍🏫 Gestión de Instructores</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Listado de Instructores</h1>
            <button onclick="abrirModalInstructor()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Nuevo Instructor
            </button>
        </div>

        <div class="overflow-x-auto bg-white p-4 rounded shadow">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Correo</th>
                        <th class="px-4 py-2 text-left">Teléfono</th>
                        <th class="px-4 py-2 text-left">Coordinaciones</th>
                        <th class="px-4 py-2 text-left">Cursos</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($instructores as $instructor)
                        @php $tieneVencido = $instructor->documentosVencidos()->count() > 0; @endphp
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-2 font-semibold {{ $tieneVencido ? 'text-red-600' : 'text-gray-800' }}">
                                {{ $instructor->nombre }}
                            </td>
                            <td class="px-4 py-2">{{ $instructor->correo ?: '—' }}</td>
                            <td class="px-4 py-2">{{ $instructor->telefono ?: '—' }}</td>
                            <td class="px-4 py-2">{{ $instructor->coordinaciones->pluck('nombre')->join(', ') ?: '—' }}</td>
                            <td class="px-4 py-2">{{ $instructor->cursos->pluck('nombre')->join(', ') ?: '—' }}</td>
                            <td class="px-4 py-2 flex flex-wrap gap-2">
                                <button onclick='editarInstructor(@json($instructor))' class="text-blue-600 hover:underline">Editar</button>
                                <a href="{{ route('admin.instructores.documentos', $instructor->id) }}" class="text-yellow-600 hover:underline">📄 Documentos</a>
                                <form action="{{ route('admin.instructores.destroy', $instructor) }}" method="POST" onsubmit="return confirm('¿Eliminar este instructor?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-gray-500 py-4">No hay instructores registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.instructores.partials.modal-form', [
        'coordinaciones' => \App\Models\Coordinacion::all(),
        'cursos' => \App\Models\Curso::all()
    ])

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

    <script>
        function abrirModalInstructor() {
            const form = document.getElementById('formInstructor');
            form.reset();
            form.action = "{{ route('admin.instructores.store') }}";
            document.getElementById('modalTitulo').textContent = 'Registrar Instructor';
            document.getElementById('modalInstructor').showModal();
            document.getElementById('_method')?.remove();
            document.querySelectorAll('#coordinacion_ids option, #curso_ids option').forEach(opt => opt.selected = false);
        }

        function editarInstructor(data) {
            const form = document.getElementById('formInstructor');
            const url = `{{ url('admin/instructores') }}/${data.id}`;
            form.action = url;
            document.getElementById('modalTitulo').textContent = 'Editar Instructor';
            form.nombre.value = data.nombre;
            form.especialidad.value = data.especialidad || '';
            form.correo.value = data.correo || '';
            form.telefono.value = data.telefono || '';

            if (!document.getElementById('_method')) {
                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PUT';
                method.id = '_method';
                form.appendChild(method);
            }

            const coordSel = document.getElementById('coordinacion_ids');
            [...coordSel.options].forEach(option => {
                option.selected = data.coordinaciones?.some(co => co.id == option.value);
            });

            const cursoSel = document.getElementById('curso_ids');
            [...cursoSel.options].forEach(option => {
                option.selected = data.cursos?.some(cu => cu.id == option.value);
            });

            document.getElementById('modalInstructor').showModal();
        }
    </script>
</x-app-layout>
