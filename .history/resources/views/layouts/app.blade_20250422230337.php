<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: true }" class="h-full" x-cloak>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex h-screen bg-gray-100 text-gray-900">
<!-- Sidebar -->
<aside class="bg-white shadow-md transition-all duration-300"
       :class="sidebarOpen ? 'w-64' : 'w-16'">
    <div class="flex justify-between items-center p-4">
        <span class="font-bold text-xl" x-show="sidebarOpen">Panel</span>
        <button @click="sidebarOpen = !sidebarOpen"
                class="text-gray-500 hover:text-black">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round"
                      d="M4 6h16M4 12h16M4 18h16"/>
                <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <nav class="space-y-2 mt-6 px-2">
        <!-- Común para todos -->
        <a href="{{ route('dashboard') }}"
        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
        :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <x-lucide-layout-dashboard class="w-5 h-5" />
            <span x-show="sidebarOpen">Inicio</span>
        </a>

        <a href="{{ route('admin.programaciones.index') }}"
        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
        :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <x-lucide-calendar-range class="w-5 h-5" />
            <span x-show="sidebarOpen">Programación</span>
        </a>

        <a href="{{ route('admin.grupos.index') }}"
        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
        :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <x-lucide-users class="w-5 h-5" />
            <span x-show="sidebarOpen">Grupos</span>
        </a>

        <a href="{{ route('admin.cursos.index') }}"
        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
        :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <x-lucide-book class="w-5 h-5" />
            <span x-show="sidebarOpen">Cursos</span>
        </a>

        <a href="{{ route('admin.aulas.index') }}"
        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
        :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <x-lucide-building class="w-5 h-5" />
            <span x-show="sidebarOpen">Aulas</span>
        </a>

        <a href="{{ route('admin.instructores.index') }}"
        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
        :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <x-lucide-user-check class="w-5 h-5" />
            <span x-show="sidebarOpen">Instructores</span>
        </a>

        <!-- Solo para administradores -->
        @if (Auth::user()->esAdministrador())
            <hr class="my-2">
            <a href="{{ route('admin.coordinaciones.index') }}"
            class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <x-lucide-compass class="w-5 h-5" />
                <span x-show="sidebarOpen">Coordinaciones</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
            class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <x-lucide-users-cog class="w-5 h-5" />
                <span x-show="sidebarOpen">Usuarios</span>
            </a>

            <a href="{{ route('admin.feriados.index') }}"
            class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <x-lucide-calendar-clock class="w-5 h-5" />
                <span x-show="sidebarOpen">Días Feriados</span>
            </a>

            <a href="{{ route('admin.auditorias.index') }}"
            class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <x-lucide-file-search class="w-5 h-5" />
                <span x-show="sidebarOpen">Auditorías</span>
            </a>
        @endif
    </nav>

</aside>
<!-- Main -->
<main class="flex-1 overflow-y-auto">
    <div class="p-4">
        {{ $slot }}
    </div>
</main>
@livewireScripts
</body>
</html>
