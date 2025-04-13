<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Panel de administración de roles
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 text-green-600">
                    {{ session('success') }}
                </div>
            @endif

            <table class="w-full table-auto border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Nombre</th>
                        <th class="px-4 py-2">Correo</th>
                        <th class="px-4 py-2">Rol actual</th>
                        <th class="px-4 py-2">Cambiar rol</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="text-center border-t">
                            <td class="px-4 py-2">{{ $user->name }}</td>
                            <td class="px-4 py-2">{{ $user->email }}</td>
                            <td class="px-4 py-2">{{ $user->rol }}</td>
                            <td class="px-4 py-2">
                                <form method="POST" action="{{ route('users.updateRole', $user) }}">
                                    @csrf
                                    @method('PUT')
                                    <select name="rol" class="border rounded px-2 py-1">
                                        @foreach (['administrador', 'analista', 'coordinador', 'instructor'] as $rol)
                                            <option value="{{ $rol }}" {{ $user->rol === $rol ? 'selected' : '' }}>{{ ucfirst($rol) }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="ml-2 px-3 py-1 bg-blue-500 text-white rounded">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
