<!-- Modal para crear un nuevo usuario -->
<div id="createUserModal" class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg w-1/3">
        <h3 class="text-xl font-semibold mb-4">Crear Usuario</h3>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <!-- Nombre -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input type="text" id="name" name="name" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>

            <!-- Rol -->
            <div class="mb-4">
                <label for="rol" class="block text-sm font-medium text-gray-700">Rol</label>
                <select id="rol" name="rol" class="mt-1 block w-full border rounded px-3 py-2" required>
                    <option value="administrador">Administrador</option>
                    <option value="coordinador">Coordinador</option>
                    <option value="analista">Analista</option>
                    <option value="instructor">Instructor</option>
                </select>
            </div>

            <!-- Activo (checkbox) -->
            <div class="mb-4 flex items-center">
                <label for="is_active" class="text-sm font-medium text-gray-700 mr-2">Activo</label>
                <input type="checkbox" id="is_active" name="is_active" value="1" class="mt-1">
            </div>

            <!-- Nueva Contraseña -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" id="password" name="password" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>

            <!-- Confirmar Contraseña -->
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Crear Usuario</button>
                <button type="button" onclick="closeCreateModal()" class="bg-gray-300 text-black px-4 py-2 rounded ml-2">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeCreateModal() {
        document.getElementById('createUserModal').classList.add('hidden');
    }
</script>
