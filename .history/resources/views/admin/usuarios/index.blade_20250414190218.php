<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Gestión de Usuarios
        </h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <div class="mb-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Usuarios Registrados</h1>
            <a href="{{ route('users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ Agregar Nuevo Usuario</a>
        </div>

        <!-- Tabla de usuarios -->
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2">Nombre</th>
                        <th class="px-4 py-2">Correo Electrónico</th>
                        <th class="px-4 py-2">Rol</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $user->name }}</td>
                            <td class="px-4 py-2">{{ $user->email }}</td>
                            <td class="px-4 py-2">{{ ucfirst($user->rol) }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:underline">Editar</a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este usuario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                                <form action="{{ route('users.reset-password', $user) }}" method="POST" onsubmit="return confirm('¿Deseas resetear la contraseña?')">
                                    @csrf
                                    <button class="text-yellow-600 hover:underline">Resetear Contraseña</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
