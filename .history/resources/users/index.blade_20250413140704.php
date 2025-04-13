@extends('layouts.app')

@section('content')
    <div class="container mx-auto mt-4">
        <h1 class="text-2xl font-bold mb-4">Gestión de Usuarios</h1>

        @if(session('success'))
            <div class="bg-green-200 text-green-800 p-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <table class="table-auto w-full">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Rol</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $user->name }}</td>
                    <td class="px-4 py-2">{{ $user->email }}</td>
                    <td class="px-4 py-2">{{ $user->rol }}</td>
                    <td class="px-4 py-2">
                        <form action="{{ route('users.updateRole', $user) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <select name="rol" class="border px-2 py-1 rounded">
                                <option value="administrador" {{ $user->rol === 'administrador' ? 'selected' : '' }}>Administrador</option>
                                <option value="analista" {{ $user->rol === 'analista' ? 'selected' : '' }}>Analista</option>
                                <option value="coordinador" {{ $user->rol === 'coordinador' ? 'selected' : '' }}>Coordinador</option>
                                <option value="instructor" {{ $user->rol === 'instructor' ? 'selected' : '' }}>Instructor</option>
                            </select>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded ml-2">
                                Actualizar
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
