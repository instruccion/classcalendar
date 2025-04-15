<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Crear Usuario
        </h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <form action="{{ route('users.store') }}" method="POST" class="bg-white shadow p-6 rounded-lg">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="
