<dialog id="modalInstructor" class="w-full max-w-3xl p-0 rounded-lg shadow-lg backdrop:bg-black/30">
    <div class="bg-white p-6">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h2 class="text-xl font-bold" id="modalTitulo">Registrar Instructor</h2>
            <button onclick="document.getElementById('modalInstructor').close()" class="text-gray-600 hover:text-black text-xl">&times;</button>
        </div>

        <form method="POST" id="formInstructor" action="{{ route('admin.instructores.store') }}" class="grid grid-cols-12 gap-4">
            @csrf

            <div class="col-span-12 md:col-span-6">
                <label class="block font-semibold mb-1">Nombre</label>
                <input type="text" name="nombre" id="nombre" required maxlength="100" class="w-full border px-4 py-2 rounded">
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="block font-semibold mb-1">Especialidad</label>
                <input type="text" name="especialidad" id="especialidad" maxlength="100" class="w-full border px-4 py-2 rounded">
            </div>

            <!-- Correo no requerido -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-semibold mb-1">Correo electrónico (opcional)</label>
                <input type="email" name="correo" id="correo" maxlength="100" class="w-full border px-4 py-2 rounded">
            </div>

            <!-- Teléfono no requerido -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-semibold mb-1">Teléfono (opcional)</label>
                <input type="text" name="telefono" id="telefono" maxlength="20" class="w-full border px-4 py-2 rounded">
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="block font-semibold mb-1">Coordinaciones</label>
                <select name="coordinacion_ids[]" id="coordinacion_ids" multiple class="w-full border px-3 py-2 rounded">
                    @foreach($coordinaciones as $coordinacion)
                        <option value="{{ $coordinacion->id }}">{{ $coordinacion->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-12 md:col-span-6">
                <label class="block font-semibold mb-1">Cursos que puede dictar</label>
                <select name="curso_ids[]" id="curso_ids" multiple class="w-full border px-3 py-2 rounded">
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}">{{ $curso->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-12 text-center mt-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Guardar Instructor
                </button>
            </div>
        </form>
    </div>
</dialog>
