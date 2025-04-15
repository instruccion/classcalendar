<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">🧭 Coordinaciones</h2>
    </x-slot>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('admin.coordinaciones.create') }}" class="mb-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        ➕ Nueva Coordinación
    </a>

    <div class="bg-white shadow rounded p-4">
        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">Color</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coordinaciones as $coord)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $coord->nombre }}</td>
                        <td class="px-4 py-2">
                            <span class="inline-block w-6 h-6 rounded-full border border-gray-400" style="background-color: {{ $coord->color }}"></span>
                            {{ $coord->color }}
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ route('coordinaciones.edit', $coord) }}" class="text-blue-600 hover:underline">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center py-4 text-gray-500">No hay coordinaciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
