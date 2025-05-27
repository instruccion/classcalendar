<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            {{ __('Editar Perfil') }}
        </h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            

                <!-- Campo Nombre -->
                <div class="col-span-1">
                    <label for="name" class="block font-medium text-gray-700">{{ __('Nombre') }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Campo Email -->
                <div class="col-span-1">
                    <label for="email" class="block font-medium text-gray-700">{{ __('Correo electrónico') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Campo Coordinación -->
                @if (Auth::user()->rol === 'administrador')
                    <div class="col-span-1">
                        <label for="coordinacion_id" class="block font-medium text-gray-700">{{ __('Coordinación') }}</label>
                        <select id="coordinacion_id" name="coordinacion_id" class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Seleccione una coordinación</option>
                            @foreach($coordinaciones as $coordinacion)
                                <option value="{{ $coordinacion->id }}" {{ old('coordinacion_id', $user->coordinacion_id) == $coordinacion->id ? 'selected' : '' }}>
                                    {{ $coordinacion->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Otros campos aquí -->
            </div>

            <div class="mt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    {{ __('Actualizar Perfil') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
