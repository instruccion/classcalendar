<dialog id="modalEditarCurso" class="rounded-lg w-full max-w-4xl p-0 overflow-hidden shadow-xl backdrop:bg-black/30">
    <div class="bg-white p-6">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h2 class="text-xl font-bold">Editar Curso</h2>
            <button onclick="document.getElementById('modalEditarCurso').close()"
                    class="text-gray-600 hover:text-black text-xl">×</button>
        </div>

        <form id="formEditarCurso" method="POST" class="grid grid-cols-12 gap-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="curso_id" id="curso_edit_id">

            <!-- Grupo(s) -->
            <div class="col-span-12">
                <label class="block font-semibold mb-1">Asignar a Grupo(s)</label>
                <div id="editar_grupo_checkboxes" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 max-h-40 overflow-y-auto p-2 border rounded">
                    {{-- Se llenará dinámicamente con JavaScript --}}
                </div>
                <div id="editar_grupo_error" class="text-red-500 text-xs mt-1 hidden">Debe seleccionar al menos un grupo</div>
            </div>

            <!-- Nombre -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-semibold mb-1">Nombre del Curso</label>
                <input type="text" name="nombre" id="curso_edit_nombre" required
                    class="w-full border px-4 py-2 rounded">
            </div>

            <!-- Tipo -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-semibold mb-1">Tipo</label>
                <select name="tipo" id="curso_edit_tipo" class="w-full border px-4 py-2 rounded" required>
                    <option value="inicial">Inicial</option>
                    <option value="recurrente">Recurrente</option>
                    <option value="puntual">Puntual</option>
                </select>
            </div>

            <!-- Duración -->
            <div class="col-span-12 md:col-span-2">
                <label class="block font-semibold mb-1">Duración (h)</label>
                <input type="number" name="duracion_horas" id="curso_edit_duracion" required min="1"
                    class="w-full border px-4 py-2 rounded">
            </div>

            <!-- Descripción -->
            <div class="col-span-12">
                <label class="block font-semibold mb-1">Descripción</label>
                <textarea name="descripcion" id="curso_edit_descripcion" rows="3"
                    class="w-full border px-4 py-2 rounded"></textarea>
            </div>

            <div class="col-span-12 flex justify-end mt-4">
                <button type="button" onclick="document.getElementById('modalEditarCurso').close()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300 mr-2">Cancelar</button>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Actualizar Curso
                </button>
            </div>
        </form>
    </div>
</dialog>
