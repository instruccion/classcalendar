<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">✏️ Editar Curso</h2>
    </x-slot>

    <div class="bg-white p-6 rounded shadow max-w-4xl mx-auto">
        <form action="{{ route('cursos.update', $curso) }}" method="POST" class="grid grid-cols-12 gap-4">
            @csrf
            @method('PUT')

            {{-- Asignación a grupos --}}
            <div class="col-span-12">
                <label class="block font-semibold mb-1">Asignar a Grupo(s)</label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                    @foreach ($grupos as $g)
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="grupo_ids[]" value="{{ $g->id }}"
                                {{ in_array($g->id, $curso->grupos->pluck('id')->toArray()) ? 'checked' : '' }}>
                            {{ $g->nombre }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="block font-semibold mb-1">Nombre del Curso</label>
                <input type="text" name="nombre" required maxlength="100"
                    value="{{ old('nombre', $curso->nombre) }}"
                    class="w-full border px-4 py-2 rounded">
            </div>

            <div class="col-span-12 md:col-span-4">
                <label class="block font-semibold mb-1">Tipo</label>
                <select name="tipo" class="w-full border px-4 py-2 rounded" required>
                    @foreach (['inicial' => 'Inicial', 'recurrente' => 'Periódico', 'puntual' => 'General'] as $valor => $label)
                        <option value="{{ $valor }}" {{ $curso->tipo === $valor ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-12 md:col-span-2">
                <label class="block font-semibold mb-1">Duración (horas)</label>
                <input type="number" name="duracion_horas" required min="1"
                    value="{{ old('duracion_horas', $curso->duracion_horas) }}"
                    class="w-full border px-4 py-2 rounded">
            </div>

            <div class="col-span-12">
                <label class="block font-semibold mb-1">Descripción</label>
                <textarea name="descripcion" rows="3"
                    class="w-full border px-4 py-2 rounded">{{ old('descripcion', $curso->descripcion) }}</textarea>
            </div>

            <div class="col-span-12 text-center mt-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Actualizar Curso
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
