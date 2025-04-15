<!-- resources/views/admin/usuarios/create.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Crear Nuevo Usuario
        </h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full border rounded px-3 py-2" required>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" class="mt-1 block w-full border rounded px-3 py-2" required>
                </div>

                <div>
                    <label for="rol" class="block text-sm font-medium text-gray-700">Rol</label>
                    <select name="rol" id="rol" class="mt-1 block w-full border rounded px-3 py-2">
                        <option value="administrador">Administrador</option>
                        <option value="coordinador">Coordinador</option>
                        <option value="analista">Analista</option>
                        <option value="instructor">Instructor</option>
                    </select>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Crear Usuario
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
