<form method="POST" action="{{ route('admin.cursos.store') }}" class="space-y-4">
    @csrf

    {{-- Asignar Grupos --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Asignar a Grupo(s)</label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 border p-4 rounded">
            @forelse ($grupos as $grupo)
                <label class="inline-flex items-center space-x-2">
                    <input type="checkbox" name="grupos[]" value="{{ $grupo->id }}"
                           class="form-checkbox text-indigo-600 rounded"
                           {{ in_array($grupo->id, old('grupos', [])) ? 'checked' : '' }}>
                    <span>{{ $grupo->nombre }}</span>
                </label>
            @empty
                <p class="text-gray-500 text-sm col-span-full">No hay grupos disponibles.</p>
            @endforelse
        </div>
        @error('grupos') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Nombre del Curso --}}
    <div>
        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Curso</label>
        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
               class="mt-1 block w-full border px-4 py-2 rounded"
               required>
        @error('nombre') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Tipo y Duración --}}
    <div class="flex gap-4">
        <div class="flex-1">
            <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
            <select name="tipo" id="tipo" class="mt-1 block w-full border px-4 py-2 rounded" required>
                <option value="">Seleccione tipo...</option>
                <option value="Presencial" {{ old('tipo') == 'Presencial' ? 'selected' : '' }}>Presencial</option>
                <option value="Virtual" {{ old('tipo') == 'Virtual' ? 'selected' : '' }}>Virtual</option>
                <option value="Mixto" {{ old('tipo') == 'Mixto' ? 'selected' : '' }}>Mixto</option>
            </select>
            @error('tipo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div style="width: 150px;">
            <label for="duracion_horas" class="block text-sm font-medium text-gray-700">Duración (h)</label>
            <input type="number" name="duracion_horas" id="duracion_horas" min="1"
                   value="{{ old('duracion_horas') }}"
                   class="mt-1 block w-full border px-4 py-2 rounded"
                   required>
            @error('duracion_horas') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Descripción --}}
    <div>
        <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción (opcional)</label>
        <textarea name="descripcion" id="descripcion" rows="3"
                  class="mt-1 block w-full border px-4 py-2 rounded">{{ old('descripcion') }}</textarea>
        @error('descripcion') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Botones --}}
    <div class="flex justify-end gap-2 pt-4 border-t">
        <button type="button" onclick="document.getElementById('modalNuevoCurso').close()"
                class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">
            Cancelar
        </button>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Registrar Curso
        </button>
    </div>
</form>
