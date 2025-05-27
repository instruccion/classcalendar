<x-app-layout>
    <div class="py-6 max-w-7xl mx-auto">
        {{-- Título y filtros superiores --}}
        {{-- (Todo igual hasta llegar a la tabla) --}}

        {{-- Tabla de programaciones agrupadas --}}
        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full table-auto text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Curso</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Duración</th>
                        <th class="px-4 py-2 text-left">Fecha Inicio</th> {{-- NUEVA --}}
                        <th class="px-4 py-2 text-left">Fecha Fin</th>    {{-- NUEVA --}}
                        <th class="px-4 py-2 text-left">Aula</th>
                        <th class="px-4 py-2 text-left">Instructor</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($programacionesAgrupadas as $grupoNombre => $bloques)
                        {{-- Nombre del Grupo --}}
                        <tr class="bg-green-50 font-semibold border-t border-green-200">
                            <td colspan="8" class="px-4 py-2">Grupo: {{ $grupoNombre }}</td>
                        </tr>

                        @foreach ($bloques as $bloqueCodigo => $items)
                            {{-- Nombre del Bloque --}}
                            <tr class="bg-gray-50 text-gray-700 border-t">
                                <td colspan="8" class="flex justify-between items-center px-4 py-2">
                                    <div>
                                        <span class="font-semibold italic underline">Bloque:</span>
                                        <span class="italic underline">{{ $bloqueCodigo ?: '—' }}</span>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.programaciones.bloque.edit', ['grupo' => $items->first()->grupo_id, 'bloque_codigo' => $bloqueCodigo ?: '_sin_codigo_']) }}"
                                           class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-2 py-1 rounded shadow">
                                            <i class="mdi mdi-pencil"></i> Editar Bloque
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            {{-- Cursos del Bloque --}}
                            @foreach ($items as $programacion)
                                <tr class="border-t">
                                    <td class="px-4 py-2">{{ $programacion->curso->nombre }}</td>
                                    <td class="px-4 py-2">{{ ucfirst($programacion->curso->tipo ?? '-') }}</td>
                                    <td class="px-4 py-2">{{ $programacion->curso->duracion_horas }}h</td>
                                    <td class="px-4 py-2">{{ $programacion->fecha_inicio?->format('d/m/Y') ?? '—' }}</td> {{-- NUEVA --}}
                                    <td class="px-4 py-2">{{ $programacion->fecha_fin?->format('d/m/Y') ?? '—' }}</td>     {{-- NUEVA --}}
                                    <td class="px-4 py-2">{{ $programacion->aula->nombre ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        @if($programacion->instructor)
                                            <div class="flex items-center gap-2">
                                                <a href="mailto:{{ $programacion->instructor->correo }}" class="text-[#00AF40] hover:underline">
                                                    {{ $programacion->instructor->nombre }}
                                                </a>
                                                <form method="POST" action="{{ route('admin.programaciones.enviarCorreo', $programacion) }}">
                                                    @csrf
                                                    <button type="submit" title="Enviar correo al instructor" class="text-blue-600 hover:text-blue-800 text-sm">
                                                        <i class="mdi mdi-email-outline text-lg"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-2 flex gap-2">
                                        <a href="{{ route('admin.programaciones.edit', $programacion) }}" class="text-[#00AF40] hover:underline text-sm">Editar</a>
                                        <form action="{{ route('admin.programaciones.destroy', $programacion) }}" method="POST" onsubmit="return confirm('¿Eliminar esta programación?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-sm">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-gray-500">No hay programaciones disponibles.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $programaciones->appends(request()->query())->links() }}
        </div>
    </div>
</x-app-layout>
