<dialog id="modalNuevoCurso" class="rounded-lg w-full max-w-4xl p-0 overflow-hidden shadow-xl backdrop:bg-black/30">
    <div class="bg-white p-6">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h2 class="text-xl font-bold">Registrar Nuevo Curso</h2>
            <button onclick="document.getElementById('modalNuevoCurso').close()"
                    class="text-gray-600 hover:text-black text-xl">×</button>
        </div>

        {{-- AQUÍ ESTÁ LA CORRECCIÓN --}}
        <form action="{{ route('admin.cursos.store') }}" method="POST" class="grid grid-cols-12 gap-4">
            @csrf

            <!-- Grupo(s) -->
            <div class="col-span-12">
                <label class="block font-semibold mb-1">Asignar a Grupo(s)</label>
                {{-- Es importante asegurarse que $grupos esté disponible aquí --}}
                {{-- Si este modal se incluye en una vista donde $grupos no se pasa, dará error --}}
                {{-- Considera pasar $grupos explícitamente al incluir o usar un View Composer --}}
                @if (!isset($grupos) || $grupos->isEmpty())
                    <p class="text-sm text-gray-500">No hay grupos disponibles o no se cargaron para este modal.</p>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 max-h-40 overflow-y-auto p-2 border rounded"> {{-- Añadido scroll si hay muchos grupos --}}
                        @foreach ($grupos as $g)
                            <label class="inline-flex items-center gap-2 text-sm"> {{-- Texto más pequeño --}}
                                <input type="checkbox" name="grupo_ids[]" value="{{ $g->id }}"
                                       class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" {{-- Clases comunes para checkboxes --}}
                                       {{ is_array(old('grupo_ids')) && in_array($g->id, old('grupo_ids')) ? 'checked' : '' }}> {{-- Mantener selección si falla validación --}}
                                {{ $g->nombre }}
                            </label>
                        @endforeach
                    </div>
                @endif
                 @error('grupo_ids') {{-- Mostrar error de validación --}}
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nombre del curso -->
            <div class="col-span-12 md:col-span-6">
                <label for="curso_nombre" class="block font-semibold mb-1">Nombre del Curso</label> {{-- Añadido for --}}
                <input type="text" id="curso_nombre" name="nombre" required maxlength="100"
                       value="{{ old('nombre') }}"
                       class="w-full border px-4 py-2 rounded @error('nombre') border-red-500 @enderror"> {{-- Resaltar error --}}
                @error('nombre')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo -->
            <div class="col-span-12 md:col-span-4">
                <label for="curso_tipo" class="block font-semibold mb-1">Tipo</label> {{-- Añadido for --}}
                <select id="curso_tipo" name="tipo" class="w-full border px-4 py-2 rounded @error('tipo') border-red-500 @enderror" required>
                    <option value="" disabled {{ old('tipo') ? '' : 'selected' }}>Seleccione tipo...</option> {{-- Opción placeholder --}}
                    <option value="inicial" {{ old('tipo') === 'inicial' ? 'selected' : '' }}>Inicial</option>
                    <option value="recurrente" {{ old('tipo') === 'recurrente' ? 'selected' : '' }}>Recurrente</option>
                    <option value="puntual" {{ old('tipo') === 'puntual' ? 'selected' : '' }}>Puntual</option>
                </select>
                 @error('tipo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Duración -->
            <div class="col-span-12 md:col-span-2">
                <label for="curso_duracion" class="block font-semibold mb-1">Duración (h)</label> {{-- Añadido for y (h) --}}
                <input type="number" id="curso_duracion" name="duracion_horas" required min="1"
                       value="{{ old('duracion_horas') }}"
                       class="w-full border px-4 py-2 rounded @error('duracion_horas') border-red-500 @enderror">
                 @error('duracion_horas')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descripción -->
            <div class="col-span-12">
                <label for="curso_descripcion" class="block font-semibold mb-1">Descripción (opcional)</label> {{-- Añadido for --}}
                <textarea id="curso_descripcion" name="descripcion" rows="3"
                          class="w-full border px-4 py-2 rounded @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
                 @error('descripcion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botón -->
            <div class="col-span-12 flex justify-end mt-4"> {{-- Alineado a la derecha --}}
                <button type="button" onclick="document.getElementById('modalNuevoCurso').close()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300 mr-2">Cancelar</button>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Registrar Curso
                </button>
            </div>
        </form>
    </div>
</dialog>
