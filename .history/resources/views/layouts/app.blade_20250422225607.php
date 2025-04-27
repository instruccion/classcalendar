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
        <a href="{{ route('dashboard') }}"
           class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
           :class="sidebarOpen ? 'justify-start' : 'justify-center'">
           <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3V3zm11 0h7v4h-7V3zm0 7h7v11h-7V10zM3 14h7v7H3v-7z" />
            </svg>

            <span x-show="sidebarOpen">Inicio</span>
        </a>
        <a href="{{ route('admin.programaciones.index') }}"
           class="flex items-center space-x-3 p-2 rounded hover:bg-gray-200"
           :class="sidebarOpen ? 'justify-start' : 'justify-center'">
           <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 19h14M5 15h14M3 7h18a2 2 0 012 2v12a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z" />
            </svg>

            <span x-show="sidebarOpen">Programación</span>
        </a>
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
