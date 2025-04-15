<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Detalles del Usuario
        </h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <div class="bg-white shadow sm:rounded-lg p-4">
            <h3 class="text-lg font-semibold">Información del Usuario</h3>
            <table class="min-w-full mt-4">
                <tr>
                    <th class="px-4 py-2 text-gray-600">Nombre</th>
                    <td class="px-4 py-2">{{ $user->name }}</td>
                </tr>
                <tr>
                    <th class="px-4 py-2 text-gray-600">Email</th>
                    <td class="px-4 py-2">{{ $user->email }}</td>
                </tr>
                <tr>
                    <th class="px-4 py-2 text-gray-600">Rol</th>
                    <td class="px-4 py-2">{{ ucfirst($user->rol) }}</td>
                </tr>
                <tr>
                    <th class="px-4 py-2 text-gray-600">Activo</th>
                    <td class="px-4 py-2">{{ $user->is_active ? 'Sí' : 'No' }}</td>
                </tr>
            </table>
        </div>
    </div>
</x-app-layout>
