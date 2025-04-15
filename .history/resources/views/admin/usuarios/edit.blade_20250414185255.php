<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Usuario
        </h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <form method="POST" action="{{ route('users.update', $user->id) }}" class="space-y-4">
            @csrf
            @method('PUT')  <!-- Indicamos que es un PUT para la actualización -->

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="rol" class="block text-sm font-medium text-gray-700">Rol</label>
                    <select id="rol" name="rol" class="mt-1 block w-full border rounded px-3 py-2" required>
                        <option value="administrador" @selected(old('rol', $user->rol) == 'administrador')>Administrador</option>
                        <option value="coordinador" @selected(old('rol', $user->rol) == 'coordinador')>Coordinador</option>
                        <option value="analista" @selected(old('rol', $user->rol) == 'analista')>Analista</option>
                        <option value="instructor" @selected(old('rol', $user->rol) == 'instructor')>Instructor</option>
                    </select>
                    @error('rol') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700">Activo</label>
                    <select name="is_active" id="is_active" class="mt-1 block w-full border rounded px-3 py-2">
                        <option value="1" @selected(old('is_active', $user->is_active) == 1)>Activo</option>
                        <option value="0" @selected(old('is_active', $user->is_active) == 0)>Inactivo</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <label for="password" class="block text-sm font-medium text-gray-700">Nueva Contraseña (opcional)</label>
                    <input type="password" id="password" name="password" class="mt-1 block w-full border rounded px-3 py-2">
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña (opcional)</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="mt-1 block w-full border rounded px-3 py-2">
                </div>

            </div>

            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
