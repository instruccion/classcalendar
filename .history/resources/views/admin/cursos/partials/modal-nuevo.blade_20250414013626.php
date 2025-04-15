@props(['grupos' => []])

<dialog id="modalNuevoCurso" class="rounded-lg w-full max-w-4xl p-0 overflow-hidden shadow-xl backdrop:bg-black/30">
    <div class="bg-white p-6">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h2 class="text-xl font-bold">Registrar Nuevo Curso</h2>
            <button onclick="document.getElementById('modalNuevoCurso').close()"
                    class="text-gray-600 hover:text-black text-xl">&times;</button>
        </div>

        <form action="{{ route('cursos.store') }}" method="POST" class="grid grid-cols-12 gap-4">
            @csrf

            {{-- Asignar grupos --}}
            <div class="col-span-12">
                <label class="block font-semibold mb-1">Asignar a Grupo(s)</label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                    @forelse ($grupos as $grupo)
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="grupo_ids[]" value="{{ $grupo->id }}">
                            {{ $grupo->nombre }}
                        </label>
                    @empty
                        <p class="text-gray-500 col-span-12">No hay grupos disponibles.</p>
                    @endforelse
                </div>
            </div>

            {{-- Nombre del curso --}}
            <div class="col-span-12 md:col-span-6">
                <label class="block font-semibold mb-1">Nombre del Curso</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" maxlength="100" required
                       class="w-full border px-4 py-2 rounded">
            </div>

            {{-- Tipo del curso --}}
            <div class="col-span-12 md:col-span-4">
                <label class="block font-semibold mb-1">Tipo</label>
                <select name="tipo" class="w-full border px-4 py-2 rounded" required>
                    <option value="inicial" {{ old('tipo') === 'inicial' ? 'selected' : '' }}>Inicial</option>
                    <option value="recurrente" {{ old('tipo') === 'recurrente' ? 'selected' : '' }}>Periódico</option>
                    <option value="puntual" {{ old('tipo') === 'puntual' ? 'selected' : '' }}>General</option>
                </select>
            </div>

            {{-- Duración --}}
            <div class="col-span-12 md:col-span-2">
                <label class="block font-semibold mb-1">Duración (horas)</label>
                <input type="number" name="duracion_horas" value="{{ old('duracion_horas') }}" required min="1"
                       class="w-full border px-4 py-2 rounded">
            </div>

            {{-- Descripción opcional --}}
            <div class="col-span-12">
                <label class="block font-semibold mb-1">Descripción (opcional)</label>
                <textarea name="descripcion" rows="3" class="w-full border px-4 py-2 rounded">{{ old('descripcion') }}</textarea>
            </div>

            {{-- Botón de enviar --}}
            <div class="col-span-12 text-center mt-4">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Registrar Curso
                </button>
            </div>
        </form>
    </div>
</dialog>
