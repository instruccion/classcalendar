<x-app-layout>
    <div class="py-6 max-w-7xl mx-auto">
        {{-- Título y filtros superiores con Mes y Año centrados --}}
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
            <form method="GET" action="{{ route('admin.programaciones.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4">

                @if(auth()->user()->esAdministrador() && is_null(auth()->user()->coordinacion_id))
                    <div class="md:col-span-1">
                        <label for="coordinacion_id" class="block text-sm text-gray-700 mb-1">Coordinación</label>
                        <select name="coordinacion_id" id="coordinacion_id" class="w-full border px-4 py-2 rounded min-w-[19rem] text-sm" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($coordinaciones as $coordinacion)
                                <option value="{{ $coordinacion->id }}" {{ request('coordinacion_id') == $coordinacion->id ? 'selected' : '' }}>
                                    {{ $coordinacion->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="md:col-span-1">
                    <label for="grupo_id" class="block text-sm text-gray-700 mb-1">Grupo</label>
                    <select name="grupo_id" id="grupo_id" class="w-full border px-4 py-2 rounded min-w-[19rem] text-sm" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-1 flex items-end justify-end ml-auto">
                    <div class="relative w-full">
                        <input type="text" name="buscar" id="buscar" placeholder="Buscar" value="{{ request('buscar') }}"
                            class="w-full border px-4 py-2 pl-4 pr-10 rounded-full text-sm" />
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabla de programaciones agrupadas --}}
        <div class="bg-white rounded shadow overflow-x-auto">
            @forelse ($programacionesAgrupadas as $grupoNombre => $bloques)
                <div class="px-4 py-2 bg-green-50 font-semibold border-b border-green-200">Grupo: {{ $grupoNombre }}</div>
                @foreach ($bloques as $bloqueCodigo => $items)
                    <div class="px-4 py-2 text-sm bg-gray-50 border-b text-gray-700">Bloque: {{ $bloqueCodigo ?: '—' }}</div>
                    <table class="w-full table-auto text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Curso</th>
                                <th class="px-4 py-2 text-left">Tipo</th>
                                <th class="px-4 py-2 text-left">Duración</th>
                                <th class="px-4 py-2 text-left">Aula</th>
                                <th class="px-4 py-2 text-left">Instructor</th>
                                <th class="px-4 py-2 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $programacion)
                                <tr class="border-t">
                                    <td class="px-4 py-2">{{ $programacion->curso->nombre }}</td>
                                    <td class="px-4 py-2">{{ ucfirst($programacion->curso->tipo ?? '-') }}</td>
                                    <td class="px-4 py-2">{{ $programacion->curso->duracion_horas }}h</td>
                                    <td class="px-4 py-2">{{ $programacion->aula->nombre ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        @if($programacion->instructor)
                                            <a href="mailto:{{ $programacion->instructor->correo }}" class="text-[#00AF40] hover:underline">
                                                {{ $programacion->instructor->nombre }}
                                            </a>
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
                        </tbody>
                    </table>
                @endforeach
            @empty
                <div class="px-4 py-6 text-center text-gray-500">No hay programaciones disponibles.</div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $programaciones->appends(request()->query())->links() }}
        </div>
    </div>
</x-app-layout>
