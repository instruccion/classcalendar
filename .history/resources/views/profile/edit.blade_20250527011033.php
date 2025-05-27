<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            {{ __('Editar Perfil') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto space-y-6">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6">
            @csrf
            @method('PATCH')

            {{-- Sección de foto --}}
            <div class="flex items-center gap-6 mb-6">
                <div class="flex-shrink-0">
                    <img id="preview-img"
                         src="{{ asset('assets/images/users/' . ($user->foto_perfil ?? 'avatar-default.png')) }}"
                         class="w-28 h-28 rounded-full object-cover ring ring-indigo-500 shadow"
                         alt="Foto de perfil">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cambiar foto</label>
                    <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*"
                        class="text-sm text-gray-600
                               file:mr-4 file:py-2 file:px-4
                               file:rounded-full file:border-0
                               file:text-sm file:font-semibold
                               file:bg-indigo-50 file:text-indigo-700
                               hover:file:bg-indigo-100">
                </div>
            </div>

            {{-- Campo Nombre --}}
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- Campo Email --}}
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- Campo Coordinación (Solo editable si es administrador) --}}
            <div class="mb-4">
                <label for="coordinacion_id" class="block text-sm font-medium text-gray-700">Coordinación</label>
                @if (Auth::user()->rol === 'administrador')
                    <select id="coordinacion_id" name="coordinacion_id"
                        class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Seleccione una coordinación</option>
                        @foreach($coordinaciones as $coordinacion)
                            <option value="{{ $coordinacion->id }}"
                                {{ old('coordinacion_id', $user->coordinacion_id) == $coordinacion->id ? 'selected' : '' }}>
                                {{ $coordinacion->nombre }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" disabled readonly
                        class="w-full mt-1 bg-gray-100 text-gray-700 rounded-md border border-gray-300 shadow-sm"
                        value="{{ $user->coordinacion->nombre ?? '—' }}">
                @endif
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition">
                    Actualizar Perfil
                </button>
            </div>
        </form>
    </div>

    {{-- Script para mostrar vista previa de la imagen --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('foto_perfil');
            const preview = document.getElementById('preview-img');

            input?.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</x-app-layout>
