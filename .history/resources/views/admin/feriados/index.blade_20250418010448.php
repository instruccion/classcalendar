<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">📅 Gestión de Días Feriados</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Listado de Feriados</h1>
            <button onclick="document.getElementById('modalFeriado').showModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Nuevo Feriado
            </button>
        </div>

        <div class="bg-white rounded shadow p-4 overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Título</th>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">Recurrente</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feriados as $feriado)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $feriado->titulo }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($feriado->fecha)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">{{ $feriado->recurrente ? 'Sí' : 'No' }}</td>
                            <td class="px-4 py-2">
                                <form action="{{ route('admin.feriados.destroy', $feriado) }}" method="POST" onsubmit="return confirm('¿Eliminar este feriado?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-gray-500 py-4">No hay feriados registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <dialog id="modalFeriado" class="w-full max-w-xl p-0 rounded shadow-lg backdrop:bg-black/30">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h3 class="text-lg font-bold">Registrar Feriado</h3>
                <button onclick="document.getElementById('modalFeriado').close()" class="text-gray-600 hover:text-black text-xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.feriados.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-semibold mb-1">Título</label>
                    <input type="text" name="titulo" required maxlength="191" class="w-full border px-4 py-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Fecha</label>
                    <input type="date" name="fecha" required class="w-full border px-4 py-2 rounded">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="recurrente" value="1" id="recurrente">
                    <label for="recurrente" class="font-medium">¿Es feriado recurrente cada año?</label>
                </div>
                <div class="text-center pt-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Guardar Feriado
                    </button>
                </div>
            </form>
        </div>
    </dialog>

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
