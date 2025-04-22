<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Programación
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">



        <div class="bg-white p-6 rounded shadow-md">
            <form method="POST" action="{{ route('admin.programaciones.update', $programacion) }}" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                @csrf
                @method('PUT')

                {{-- Grupo --}}
                <div>
                    <label for="grupo_id" class="block font-semibold mb-1">Grupo <span class="text-red-500">*</span></label>
                    <select name="grupo_id" id="grupo_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un grupo...</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ $programacion->grupo_id == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin coordinación' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Curso --}}
                <div>
                    <label for="curso_id" class="block font-semibold mb-1">Curso <span class="text-red-500">*</span></label>
                    <select name="curso_id" id="curso_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un curso...</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" {{ $programacion->curso_id == $curso->id ? 'selected' : '' }}>
                                {{ $curso->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Bloque --}}
                <div class="md:col-span-2 flex items-center gap-4">
                    <label for="bloque_codigo" class="font-semibold">Bloque</label>
                    <input type="text" name="bloque_codigo" id="bloque_codigo" value="{{ $programacion->bloque_codigo }}" class="border px-4 py-2 rounded w-full md:w-1/3">
                </div>

                {{-- Fechas y horas --}}
                <div>
                    <label for="fecha_inicio" class="block font-semibold mb-1">Fecha Inicio <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $programacion->fecha_inicio->format('Y-m-d') }}" required class="w-full border px-4 py-2 rounded">
                </div>

                <div>
                    <label for="hora_inicio" class="block font-semibold mb-1">Hora Inicio <span class="text-red-500">*</span></label>
                    <input type="time" name="hora_inicio" id="hora_inicio" value="{{ $programacion->hora_inicio->format('H:i') }}" required class="w-full border px-4 py-2 rounded">
                </div>

                <div>
                    <label for="fecha_fin" class="block font-semibold mb-1">Fecha Fin <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="{{ $programacion->fecha_fin->format('Y-m-d') }}" required class="w-full border px-4 py-2 rounded">
                </div>

                <div>
                    <label for="hora_fin" class="block font-semibold mb-1">Hora Fin <span class="text-red-500">*</span></label>
                    <input type="time" name="hora_fin" id="hora_fin" value="{{ $programacion->hora_fin->format('H:i') }}" required class="w-full border px-4 py-2 rounded">
                </div>

                {{-- Aula --}}
                <div>
                    <label for="aula_id" class="block font-semibold mb-1">Aula <span class="text-red-500">*</span></label>
                    <select name="aula_id" id="aula_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un aula...</option>
                        @foreach($aulas as $aula)
                            <option value="{{ $aula->id }}" {{ $programacion->aula_id == $aula->id ? 'selected' : '' }}>
                                {{ $aula->nombre }}{{ $aula->lugar ? ' – ' . $aula->lugar : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Instructor --}}
                <div>
                    <label for="instructor_id" class="block font-semibold mb-1">Instructor</label>
                    <select name="instructor_id" id="instructor_id" class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Sin instructor</option>
                        @foreach($instructores as $instructor)
                            <option value="{{ $instructor->id }}" {{ $programacion->instructor_id == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Botón --}}
                <div class="md:col-span-2 text-center mt-6">
                    <button type="submit" class="bg-[#00AF40] text-white px-6 py-2 rounded hover:bg-green-700">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
