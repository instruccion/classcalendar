{{-- resources/views/instructores/agenda.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            🗓️ Mi Agenda
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-4">
            <p class="text-gray-600 text-center">Aquí verás los cursos que te han sido asignados como instructor.</p>

            {{-- Aquí en el futuro cargarás las programaciones asignadas al instructor --}}
        </div>
    </div>
</x-app-layout>
