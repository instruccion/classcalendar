<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: true }" class="h-full" x-cloak>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
            <!-- Icono Home -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m0 0h4m-4 0H7m4 0v-8m5 8h2a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-7-7a1 1 0 00-1.414 0l-7 7A1 1 0 003 9.414V18a2 2 0 002 2h2" />
            </svg>
            <span x-show="sidebarOpen">Inicio</span>
        </a>

        <a href="{{ route('admin.programaciones.index') }}"
        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
        :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <!-- Icono Calendario -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-12 4h12M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span x-show="sidebarOpen">Programación</span>
        </a>

        <a href="{{ route('admin.grupos.index') }}"
        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
        :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <!-- Icono Grupos -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 0a3 3 0 11-6 0 3 3 0 016 0zM3 11a3 3 0 116 0 3 3 0 01-6 0z" />
            </svg>
            <span x-show="sidebarOpen">Grupos</span>
        </a>

        <a href="{{ route('admin.cursos.index') }}"
        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
        :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <!-- Icono Libro -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4-2m-8-4v16m0-16L5 7m2-1h8m4 0v16m0-16l3 1m-3-1H7" />
            </svg>
            <span x-show="sidebarOpen">Cursos</span>
        </a>

        <a href="{{ route('admin.aulas.index') }}"
        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
        :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <!-- Icono Edificio -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M9 21V9m6 12V3m-6 6h6" />
            </svg>
            <span x-show="sidebarOpen">Aulas</span>
        </a>

        <a href="{{ route('admin.instructores.index') }}"
        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
        :class="sidebarOpen ? 'justify-start' : 'justify-center'">
            <!-- Icono Usuario -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A7.97 7.97 0 0012 20a7.97 7.97 0 006.879-2.196M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span x-show="sidebarOpen">Instructores</span>
        </a>

        <!-- Solo para administradores -->
        @if (Auth::user()->esAdministrador())
            <hr class="my-2">
            <a href="{{ route('admin.coordinaciones.index') }}"
            class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l2 7h7l-5.5 4 2 7L12 16l-5.5 4 2-7-5.5-4h7z" />
                </svg>
                <span x-show="sidebarOpen">Coordinaciones</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
            class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span x-show="sidebarOpen">Usuarios</span>
            </a>

            <a href="{{ route('admin.feriados.index') }}"
            class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M4 19h16M4 15h16" />
                </svg>
                <span x-show="sidebarOpen">Feriados</span>
            </a>

            <a href="{{ route('admin.auditorias.index') }}"
            class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
            :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405M15 17l-3-3 1-4m-2 4L4 4M4 4h16" />
                </svg>
                <span x-show="sidebarOpen">Auditoría</span>
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

</body>
</html>
