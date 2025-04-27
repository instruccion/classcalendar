<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'CursosLaser') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/layout.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.3.67/css/materialdesignicons.min.css">

    <style>
        html.sidebar-collapsed #sidebar nav span,
        html.sidebar-collapsed #sidebar h2 {
            display: none;
        }

        html.sidebar-collapsed #sidebar {
            width: 4rem !important;
        }

        html.sidebar-collapsed #main-content {
            margin-left: 4rem !important;
        }

        @media (max-width: 1023px) {
            #main-content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800">

{{-- HEADER --}}
<header class="fixed top-0 left-0 right-0 bg-white shadow-md h-16 flex items-center justify-between px-4 z-50">
    <div class="flex items-center gap-2">
        <button id="menu-toggle" class="w-10 h-10 rounded-full border flex items-center justify-center hover:bg-gray-100 lg:hidden">
            <i class="mdi mdi-menu text-xl"></i>
        </button>
        <button id="desktop-menu-toggle" class="w-10 h-10 rounded-full border items-center justify-center hover:bg-gray-100 hidden lg:flex">
            <i class="mdi mdi-menu text-xl"></i>
        </button>
    </div>

    <div class="absolute left-1/2 transform -translate-x-1/2">
        <img src="{{ asset('assets/images/logo-light.png') }}" alt="Logo" class="h-8">
    </div>

    <div class="flex items-center gap-4">
        <button id="btn-notificaciones" class="w-10 h-10 rounded-full border flex items-center justify-center hover:bg-gray-100 relative">
            <i class="mdi mdi-bell-outline text-xl"></i>
            <span id="contador-mensajes" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 rounded-full font-semibold hidden">0</span>
        </button>
        <button id="btnFullscreen" class="w-10 h-10 rounded-full border flex items-center justify-center hover:bg-gray-100">
            <i class="mdi mdi-fullscreen text-xl"></i>
        </button>

        <div class="relative">
            <div class="flex items-center gap-3 cursor-pointer" id="avatar-btn">
                <div class="text-right hidden md:block leading-tight">
                    <div class="text-gray-800 font-semibold">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-500">{{ ucfirst(Auth::user()->rol ?? 'Usuario') }}</div>
                </div>
                @php
                    $foto = Auth::user()->foto ?? null;
                    $fotoPerfil = $foto && file_exists(public_path("uploads/$foto"))
                                    ? asset("uploads/$foto")
                                    : asset("assets/images/users/avatar-default.png");
                @endphp
                <img src="{{ $fotoPerfil }}" class="w-10 h-10 rounded-full object-cover border" alt="Avatar">
            </div>

            <div id="avatar-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded shadow-lg z-50 py-1">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Salir</button>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- SIDEBAR --}}
<aside id="sidebar"
       class="fixed top-16 left-0 bottom-0 w-64 bg-white border-r border-gray-300 p-4 z-40 transition-all duration-300 ease-in-out lg:block">
    <h2 class="text-xl font-bold mb-6">Menú</h2>
    <nav class="flex flex-col gap-3 text-sm">
        <a href="{{ route('calendario.index') }}" class="hover:underline text-gray-800 flex items-center gap-2"><i class="mdi mdi-calendar-month-outline text-lg"></i> <span>Calendario</span></a>
        <a href="{{ route('admin.programaciones.index') }}" class="hover:underline text-gray-800 flex items-center gap-2"><i class="mdi mdi-timetable text-lg"></i> <span>Programaciones</span></a>
        <a href="{{ route('agenda.index') }}" class="hover:underline text-gray-800 flex items-center gap-2"><i class="mdi mdi-calendar-check text-lg"></i> <span>Agenda</span></a>
        <a href="{{ route('admin.grupos.index') }}" class="hover:underline text-gray-800 flex items-center gap-2"><i class="mdi mdi-account-group text-lg"></i> <span>Grupos</span></a>
        <a href="{{ route('admin.cursos.index') }}" class="hover:underline text-gray-800 flex items-center gap-2"><i class="mdi mdi-book-open-variant text-lg"></i> <span>Cursos</span></a>
        <a href="{{ route('admin.aulas.index') }}" class="hover:underline text-gray-800 flex items-center gap-2"><i class="mdi mdi-domain text-lg"></i> <span>Aulas</span></a>
        <a href="{{ route('admin.instructores.index') }}" class="hover:underline text-gray-800 flex items-center gap-2"><i class="mdi mdi-account-tie text-lg"></i> <span>Instructores</span></a>

        @if(auth()->user()?->rol === 'administrador')
            <a href="{{ route('admin.coordinaciones.index') }}" class="hover:underline text-gray-800 flex items-center gap-2"><i class="mdi mdi-map-marker-radius text-lg"></i> <span>Coordinaciones</span></a>
            <a href="{{ route('admin.users.index') }}" class="hover:underline text-gray-800 flex items-center gap-2"><i class="mdi mdi-account-cog text-lg"></i> <span>Usuarios</span></a>
            <a href="{{ route('admin.feriados.index') }}" class="hover:underline text-gray-800 flex items-center gap-2"><i class="mdi mdi-calendar-remove text-lg"></i> <span>Días Feriados</span></a>
            <a href="{{ route('admin.auditorias.index') }}" class="hover:underline text-gray-800 flex items-center gap-2"><i class="mdi mdi-clipboard-text-clock text-lg"></i> <span>Auditorías</span></a>
        @endif
    </nav>
</aside>

<div id="sidebar-overlay"
     class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm z-30 hidden lg:hidden transition-opacity duration-300 ease-in-out opacity-0"></div>

<main id="main-content" class="mt-16 p-6 min-h-[calc(100vh-8rem)] pb-20 transition-all duration-300 ease-in-out lg:ml-64">
    {{ $slot }}

    @if (session('success'))
        <x-toast type="success" :message="session('success')" />
    @endif

    @if (session('error'))
        <x-toast type="error" :message="session('error')" />
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <x-toast type="error" :message="$error" />
        @endforeach
    @endif
</main>

<footer class="fixed bottom-0 left-0 right-0 bg-white text-center text-sm py-2 border-t z-10">
    2025 © CursosLaser | Desarrollado por De Gouveia José
</footer>

</body>
</html>
