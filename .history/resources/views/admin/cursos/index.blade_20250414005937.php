{{-- resources/views/admin/cursos/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            📘 Gestión de Cursos
        </h2>
    </x-slot>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 shadow">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <form method="GET" class="flex gap-2">
            <select name="grupo_id" class="border px-3 py-2 rounded text-sm">
                <option value="">📂 Todos los grupos</option>
                @foreach ($grupos as $grupo)
                    <option value="{{ $grupo->id }}" {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>
                        {{ $grupo->nombre }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                🔍 Filtrar
            </button>
        </form>

        <a href="{{ route('cursos.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
            ➕ Nuevo Curso
        </a>
    </div>

    <div class="bg-white rounded shadow p-4">
        <table class="min-w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-left">Tipo</th>
                    <th class="px-4 py-2 text-left">Duración</th>
                    <th class="px-4 py-2 text-left">Grupos</th>
                    <th class="px-4 py-2 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cursos as $curso)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $curso->nombre }}</td>
                        <td class="px-4 py-2 capitalize">{{ $curso->tipo }}</td>
                        <td class="px-4 py-2">{{ $curso->duracion_horas }} h</td>
                        <td class="px-4 py-2">
                            {{ $curso->grupos->pluck('nombre')->implode(', ') }}
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ route('cursos.edit', $curso) }}" class="text-blue-600 hover:underline mr-2">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">No hay cursos registrados para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
