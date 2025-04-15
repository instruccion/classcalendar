<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Usuarios Registrados
        </h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <!-- Botón para agregar un nuevo usuario -->
        <button onclick="openCreateModal()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mb-4">
            ➕ Nuevo Usuario
        </button>

        <!-- Tabla de Usuarios -->
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Nombre</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Rol</th>
                        <th class="px-4 py-2">Activo</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $user->id }}</td>
                            <td class="px-4 py-2">{{ $user->name }}</td>
                            <td class="px-4 py-2">{{ $user->email }}</td>
                            <td class="px-4 py-2">{{ ucfirst($user->rol) }}</td>
                            <td class="px-4 py-2">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                <!-- Botón de Editar (con Modal) -->
                                <button onclick="openEditModal({{ $user->id }})" class="text-blue-600 hover:underline">Editar</button>

                                <!-- Eliminar -->
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Confirmas la eliminación del usuario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Modal de Crear Usuario -->
        <div id="createUserModal" class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 hidden">
            <div class="bg-white p-6 rounded-lg w-full max-w-xl">
                <h3 class="text-xl font-semibold mb-4">Crear Nuevo Usuario</h3>

                <form id="createUserForm" method="POST" action="{{ route('users.store') }}">
                    @csrf

                    {{-- NOMBRE --}}
                    <input type="text" name="name" required>

                    {{-- EMAIL --}}
                    <input type="email" name="email" required>

                    {{-- ROL --}}
                    <select name="rol" required>
                        <option value="administrador">Administrador</option>
                        <option value="coordinador">Coordinador</option>
                        <option value="analista">Analista</option>
                        <option value="instructor">Instructor</option>
                    </select>

                    {{-- ACTIVO --}}
                    <input type="checkbox" name="is_active" value="1" checked>

                    {{-- CONTRASEÑA --}}
                    <input type="password" name="password" required>

                    {{-- CONFIRMAR CONTRASEÑA --}}
                    <input type="password" name="password_confirmation" required>

                    <button type="submit">Crear Usuario</button>
                </form>

            </div>
        </div>


        <!-- Modal de Editar Usuario -->
        <div id="editUserModal" class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 hidden">
            <div class="bg-white p-6 rounded-lg w-1/3">
                <h3 class="text-xl font-semibold mb-4">Editar Usuario</h3>
                <form id="editUserForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editUserId" name="user_id">

                    <!-- Nombre -->
                    <div class="mb-4">
                        <label for="editName" class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" id="editName" name="name" class="mt-1 block w-full border rounded px-3 py-2" required>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="editEmail" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="editEmail" name="email" class="mt-1 block w-full border rounded px-3 py-2" required>
                    </div>

                    <!-- Rol -->
                    <div class="mb-4">
                        <label for="editRol" class="block text-sm font-medium text-gray-700">Rol</label>
                        <select id="editRol" name="rol" class="mt-1 block w-full border rounded px-3 py-2" required>
                            <option value="administrador">Administrador</option>
                            <option value="coordinador">Coordinador</option>
                            <option value="analista">Analista</option>
                            <option value="instructor">Instructor</option>
                        </select>
                    </div>

                    <!-- Activo (checkbox) -->
                    <div class="mb-4 flex items-center">
                        <label for="editIsActive" class="text-sm font-medium text-gray-700 mr-2">Activo</label>
                        <input type="checkbox" id="editIsActive" name="is_active" value="1" class="mt-1">
                    </div>

                    <!-- Nueva Contraseña -->
                    <div class="mb-4">
                        <label for="editPassword" class="block text-sm font-medium text-gray-700">Nueva Contraseña (opcional)</label>
                        <input type="password" id="editPassword" name="password" class="mt-1 block w-full border rounded px-3 py-2">
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="mb-4">
                        <label for="editPasswordConfirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña (opcional)</label>
                        <input type="password" id="editPasswordConfirmation" name="password_confirmation" class="mt-1 block w-full border rounded px-3 py-2">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Actualizar Usuario</button>
                        <button type="button" onclick="closeEditModal()" class="bg-gray-300 text-black px-4 py-2 rounded ml-2">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createUserModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createUserModal').classList.add('hidden');
        }

        function openEditModal(userId) {
            const modal = document.getElementById('editUserModal');
            modal.classList.remove('hidden');

            // Llenar el formulario con los datos del usuario
            const user = @json($users); // Pasa los usuarios al JS

            const userData = user.find(u => u.id === userId);
            document.getElementById('editUserId').value = userData.id;
            document.getElementById('editName').value = userData.name;
            document.getElementById('editEmail').value = userData.email;
            document.getElementById('editRol').value = userData.rol;
            document.getElementById('editIsActive').checked = userData.is_active;
        }

        function closeEditModal() {
            document.getElementById('editUserModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
