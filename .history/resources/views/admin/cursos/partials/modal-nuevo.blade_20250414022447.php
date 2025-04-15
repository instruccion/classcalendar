<dialog id="modalNuevoCurso" class="rounded-lg w-full max-w-4xl p-0 overflow-hidden shadow-xl backdrop:bg-black/30">
    <div class="bg-white p-6">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h2 class="text-xl font-bold">Registrar Nuevo Curso</h2>
            <button onclick="document.getElementById('modalNuevoCurso').close()" class="text-gray-600 hover:text-black text-xl">&times;</button>
        </div>

        <form action="{{ route('cursos.store') }}" method="POST" class="grid grid-cols-12 gap-4">
            @csrf

            <!-- Grupo(s) -->
            <div class="col-span-12">
                <label class="block font-semibold mb-1">Asignar a Grupo(s)</label>
                @if ($grupos->isEmpty())
                    <p class="text-sm text-gray-500">No hay grupos disponibles.</p>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach ($grupos as $g)
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="grupo_ids[]" value="{{ $g->id }}">
                                {{ $g->nombre }}
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- nombre, tipo, duración, descripción... -->

            <div class="col-span-12 text-center mt-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Registrar Curso
                </button>
            </div>
        </form>
    </div>
</dialog>
