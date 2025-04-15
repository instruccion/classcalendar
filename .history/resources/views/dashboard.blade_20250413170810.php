<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Control') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <x-card title="Usuarios registrados">
                <p class="text-3xl font-bold text-blue-600">125</p>
            </x-card>

            <x-card title="Cursos activos">
                <p class="text-3xl font-bold text-green-600">42</p>
            </x-card>

            <x-card title="Grupos abiertos">
                <p class="text-3xl font-bold text-indigo-600">8</p>
            </x-card>

        </div>
    </div>
</x-app-layout>
