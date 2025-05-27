<x-app-layout>
    <div class="py-6 max-w-7xl mx-auto">
        {{-- Título y filtros superiores --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-4">
            <div class="flex items-center gap-2 flex-wrap justify-center w-full md:w-auto">
                <h1 class="text-2xl font-bold">Programaciones</h1>
                <form method="GET" action="{{ route('admin.programaciones.index') }}" class="flex gap-2 items-center">
                    <select name="mes" id="mes" class="border px-3 py-1.5 rounded w-40 text-sm" onchange="this.form.submit()">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('mes', now()->month) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                            </option>
                        @endforeach
                    </select>
                    <select name="anio" id="anio" class="border px-3 py-1.5 rounded w-32 text-sm" onchange="this.form.submit()">
                        @for ($year = now()->year; $year >= 2020; $year--)
                            <option value="{{ $year }}" {{ request('anio', now()->year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endfor
                    </select>
                </form>
            </div>
            <a href="{{ route('admin.programaciones.create') }}" class="bg-[#00AF40] text-white px-4 py-2 rounded hover:bg-green-700 text-sm mt-4 md:mt-0">
                ➕ Nueva Programación
            </a>
        </div>

        {{-- Barra de filtros secundaria --}}
        <div class="bg-white p-4 rounded shadow-md mb-4">
            <form method="GET" action="{{ route('admin.programaciones.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
                @if(auth()->user()->esAdministrador() && is_null(auth()->user()->coordinacion_id))
                    <div class="min-w-0">
                        <label for="coordinacion_id" class="block text-sm text-gray-700 mb-1">Coordinación</label>
                        <select name="coordinacion_id" id="coordinacion_id" class="w-full max-w-[20rem] border px-4 py-2 rounded text-sm" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($coordinaciones as $coordinacion)
                                <option value="{{ $coordinacion->id }}" {{ request('coordinacion_id') == $coordinacion->id ? 'selected' : '' }}>
                                    {{ $coordinacion->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="min-w-0">
                    <label for="grupo_id" class="block text-sm text-gray-700 mb-1">Grupo</label>
                    <select name="grupo_id" id="grupo_id" class="w-full max-w-[24rem] border px-4 py-2 rounded text-sm" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-0 flex justify-start lg:justify-end">
                    <div class="relative w-full max-w-[20rem]">
                        <input type="text" name="buscar" id="buscar" placeholder="Buscar" value="{{ request('buscar') }}"
                               class="w-full border px-4 py-2 pr-10 pl-4 rounded-full text-sm" />
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabla de programaciones agrupadas --}}
        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full table-auto text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Curso</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Duración</th>
                        <th class="px-4 py-2 text-left">Fecha Inicio</th> {{-- NUEVO --}}
                        <th class="px-4 py-2 text-left">Fecha Fin</th>    {{-- NUEVO --}}
                        <th class="px-4 py-2 text-left">Aula</th>
                        <th class="px-4 py-2 text-left">Instructor</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($programacionesAgrupadas as $grupoNombre => $bloques)
                        {{-- Nombre del Grupo --}}
                        <tr class="bg-green-50 font-semibold border-t border-green-200">
                            <td colspan="8" class="px-4 py-2">Grupo: {{ $grupoNombre }}</td> {{-- Cambiar colspan de 6 a 8 --}}
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
                                    <td class="px-4 py-2">{{ $programacion->fecha_inicio?->format('d/m/Y') ?? '—' }}</td> {{-- NUEVO --}}
                                    <td class="px-4 py-2">{{ $programacion->fecha_fin?->format('d/m/Y') ?? '—' }}</td>     {{-- NUEVO --}}
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
