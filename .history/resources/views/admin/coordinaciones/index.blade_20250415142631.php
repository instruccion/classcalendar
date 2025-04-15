<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Coordinaciones</h2>
    </x-slot>

    <div class="py-4 max-w-6xl mx-auto">
        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Coordinaciones</h1>
            <button onclick="window.location.href='{{ route('admin.coordinaciones.create') }}'"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Nueva Coordinación
            </button>
        </div>

        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Color</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coordinaciones as $coor)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $coor->nombre }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-block w-6 h-6 rounded-full" style="background-color: {{ $coor->color }};"></span>
                            </td>
                            <td class="px-4 py-2 flex gap-3">
                                <!-- Editar -->
                                <a href="{{ route('admin.coordinaciones.edit', $coor) }}" class="text-blue-600 hover:underline">Editar</a>

                                <!-- Eliminar -->
                                <form action="{{ route('admin.coordinaciones.destroy', $coor) }}" method="POST" onsubmit="return confirm('¿Eliminar esta coordinación?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-4 text-gray-500">No hay coordinaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
