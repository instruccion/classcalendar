<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Usuario
        </h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                </div>

                <div>
                    <label for="rol" class="block text-sm font-medium text-gray-700">Rol</label>
                    <select name="rol" id="rol" class="mt-1 block w-full border rounded px-3 py-2">
                        <option value="administrador" @selected($user->rol === 'administrador')>Administrador</option>
                        <option value="coordinador" @selected($user->rol === 'coordinador')>Coordinador</option>
                        <option value="analista" @selected($user->rol === 'analista')>Analista</option>
                        <option value="instructor" @selected($user->rol === 'instructor')>Instructor</option>
                    </select>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
