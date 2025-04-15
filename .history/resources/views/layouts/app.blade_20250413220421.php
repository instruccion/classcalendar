<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'CursosLaser') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind + Material Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.3.67/css/materialdesignicons.min.css">
    <style>
        .sidebar-hidden #sidebar {
            margin-left: -16rem; /* 64 * 0.25rem = 16rem = 256px */
        }
        .sidebar-hidden #main-content {
            margin-left: 0;
        }
    </style>

</head>

<body class="bg-gray-100 text-gray-800">

    {{-- HEADER --}}
    <header class="fixed top-0 left-0 right-0 bg-white shadow-md h-16 flex items-center justify-between px-4 z-50">
        {{-- Botón de menú --}}
        <div class="flex items-center gap-2">
            <button id="menu-toggle" class="w-10 h-10 rounded-full border flex items-center justify-center hover:bg-gray-100">
                <i class="mdi mdi-menu text-xl"></i>
            </button>
        </div>

        {{-- Logo --}}
        <div class="absolute left-1/2 transform -translate-x-1/2">
            <img src="{{ asset('assets/images/logo-light.png') }}" alt="Logo" class="h-8">
        </div>

        {{-- Perfil + notificaciones --}}
        <div class="flex items-center gap-4">
            {{-- Notificaciones --}}
            <button id="btn-notificaciones" class="w-10 h-10 rounded-full border flex items-center justify-center hover:bg-gray-100 relative">
                <i class="mdi mdi-bell-outline text-xl"></i>
                <span id="contador-mensajes" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 rounded-full font-semibold hidden">0</span>
            </button>

            {{-- Fullscreen --}}
            <button id="btnFullscreen" class="w-10 h-10 rounded-full border flex items-center justify-center hover:bg-gray-100">
                <i class="mdi mdi-fullscreen text-xl"></i>
            </button>

            {{-- Avatar + nombre --}}
            <div class="relative">
                <div class="flex items-center gap-3 cursor-pointer" id="avatar-btn">
                    <div class="text-right hidden md:block leading-tight">
                        <div class="text-gray-800 font-semibold">{{ Auth::user()->name }}</div>
                        @php
                            $rolNombre = ucfirst(Auth::user()->rol ?? 'Usuario');
                        @endphp

                        <div class="text-xs text-gray-500">{{ $rolNombre }}</div>

                    </div>
                    @php
                        $foto = Auth::user()->foto ?? null;
                        $fotoPerfil = $foto && file_exists(public_path("uploads/$foto"))
                                        ? asset("uploads/$foto")
                                        : asset("assets/images/users/avatar-default.png");
                    @endphp
                    <img src="{{ $fotoPerfil }}" class="w-10 h-10 rounded-full object-cover border" alt="Avatar">

                </div>

                <div id="avatar-menu" class="hidden absolute right-0 mt-2 w-48 bg-white shadow rounded z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100">Perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Salir</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- SIDEBAR --}}
    <aside id="sidebar"
        <ul class="space-y-2">
        <li>
            <a href="{{ route('calendario.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-200">
                🏠 <span class="ml-2">Inicio</span>
            </a>
        </li>
        <li>
            <a href="{{ route('coordinaciones.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-200">
                📍 <span class="ml-2">Coordinaciones</span>
            </a>
        </li>
        <li>
            <a href="{{ route('cursos.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-200">
                📄 <span class="ml-2">Cursos</span>
            </a>
        </li>
        <li>
            <a href="{{ route('aulas.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-200">
                📍 <span class="ml-2">Aulas</span>
            </a>
        </li>
        <li>
            <a href="{{ route('instructores.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-200">
                🏫 <span class="ml-2">Instructores</span>
            </a>
        </li>
        <li>
            <a href="{{ route('programaciones.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-200">
                ✉️ <span class="ml-2">Programaciones</span>
            </a>
        </li>
        <li>
            <a href="{{ route('agenda.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-200">
                🗓️ <span class="ml-2">Agenda</span>
            </a>
        </li>

        @if (auth()->user() && auth()->user()->rol === 'administrador')
            <li>
                <a href="{{ route('users.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-200">
                    👥 <span class="ml-2">Usuarios</span>
                </a>
            </li>
            <li>
                <a href="{{ route('feriados.index') }}" class="flex items-center px-4 py-2 hover:bg-gray-200">
                    ❄️ <span class="ml-2">Días Feriados</span>
                </a>
            </li>
        @endif
    </ul>

    </aside>


    {{-- OVERLAY --}}
    <div id="sidebar-overlay"
        class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm z-40 hidden lg:hidden"
        style="transition: opacity 0.3s ease;"></div>


    {{-- CONTENIDO --}}
    <main id="main-content"
        class="mt-16 p-6 min-h-[calc(100vh-8rem)] pb-20 transition-all duration-300 ease-in-out ml-64">
        {{ $slot }}
    </main>



    {{-- FOOTER --}}
    <footer class="fixed bottom-0 left-0 right-0 bg-white text-center text-sm py-2 border-t">
        2025 © CursosLaser | Desarrollado por De Gouveia José
    </footer>

    {{-- Scripts --}}
    <script>
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('main-content');
        const overlay = document.getElementById('sidebar-overlay');
        const toggleBtn = document.getElementById('menu-toggle');

        toggleBtn?.addEventListener('click', () => {
            const root = document.body;

            if (window.innerWidth >= 1024) {
                // Pantalla de PC: alternamos clase al body
                root.classList.toggle('sidebar-hidden');
            } else {
                // Pantalla pequeña: mostrar como drawer
                sidebar.classList.toggle('hidden');
                overlay.classList.toggle('hidden');
            }
        });




        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            overlay.classList.add('hidden');

            if (window.innerWidth >= 1024) {
                main.classList.remove('ml-64');
                main.classList.add('ml-0');
            }
        });

        // Ajuste inicial en pantallas grandes
        document.addEventListener('DOMContentLoaded', () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                main.classList.add('ml-64');
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                main.classList.remove('ml-64');
            }
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            overlay.classList.add('hidden');
        });


        document.getElementById('avatar-btn')?.addEventListener('click', () => {
            document.getElementById('avatar-menu')?.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#avatar-btn') && !e.target.closest('#avatar-menu')) {
                document.getElementById('avatar-menu')?.classList.add('hidden');
            }
        });

        document.getElementById('btnFullscreen')?.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        });
    </script>

</body>
</html>
