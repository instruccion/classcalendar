<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Usuario
        </h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div class="col-span-1">
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                               class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                               required>
                    </div>

                    <!-- Email -->
                    <div class="col-span-1">
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                               class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                               required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Rol -->
                    <div class="col-span-1">
                        <label for="rol" class="block text-sm font-medium text-gray-700">Rol</label>
                        <select name="rol" id="rol"
                                class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="administrador" @selected($user->rol === 'administrador')>Administrador</option>
                            <option value="coordinador" @selected($user->rol === 'coordinador')>Coordinador</option>
                            <option value="analista" @selected($user->rol === 'analista')>Analista</option>
                            <option value="instructor" @selected($user->rol === 'instructor')>Instructor</option>
                        </select>
                    </div>

                    <!-- Contraseña (opcional para el administrador) -->
                    <div class="col-span-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Nueva Contraseña (Opcional)</label>
                        <input type="password" name="password" id="password"
                               class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Desactivar Usuario -->
                    <div class="col-span-1">
                        <label for="is_active" class="block text-sm font-medium text-gray-700">Estado del Usuario</label>
                        <select name="is_active" id="is_active"
                                class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="1" @selected($user->is_active == 1)>Activo</option>
                            <option value="0" @selected($user->is_active == 0)>Inactivo</option>
                        </select>
                    </div>
                </div>

                <!-- Botón de actualización -->
                <div class="text-right">
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
