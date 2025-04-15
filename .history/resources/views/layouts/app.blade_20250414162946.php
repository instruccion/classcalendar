<!DOCTYPE html>
{{-- Añadimos la clase 'sidebar-collapsed' al body si es necesario guardarla entre peticiones (ej. con JS/localStorage) --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class=""> {{-- Dejar vacío o añadir 'sidebar-collapsed' --}}
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'CursosLaser') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- RECOMENDACIÓN: Mover Tailwind y MDI a Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Temporalmente mantenemos CDN si aún no migraste a Vite --}}
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.3.67/css/materialdesignicons.min.css">

    {{-- Estilos para controlar el margen con la clase del body (MEJOR MOVER A app.css) --}}
    <style>
        /* En desktop, cuando el sidebar está colapsado, quitar margen */
        .sidebar-collapsed #main-content {
            margin-left: 0 !important; /* !important puede ser necesario para sobreescribir ml-64 */
        }
        /* En desktop, cuando el sidebar está colapsado, ocultar el sidebar */
        /* Usar transform para permitir transición */
        .sidebar-collapsed #sidebar {
             transform: translateX(-100%);
        }

        /* Asegurar que en pantallas pequeñas el margen siempre sea 0 */
        @media (max-width: 1023px) {
            #main-content {
                margin-left: 0 !important;
            }
             /* Ocultar sidebar inicialmente en móvil, pero permitir mostrarlo */
             /* #sidebar {
                transform: translateX(-100%);
             }
             #sidebar:not(.hidden) {
                transform: translateX(0);
             } */
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800">

    {{-- HEADER --}}
    <header class="fixed top-0 left-0 right-0 bg-white shadow-md h-16 flex items-center justify-between px-4 z-50">
        {{-- Botón de menú --}}
        <div class="flex items-center gap-2">
            <button id="menu-toggle" class="w-10 h-10 rounded-full border flex items-center justify-center hover:bg-gray-100 lg:hidden"> {{-- Ocultar en lg por defecto --}}
                <i class="mdi mdi-menu text-xl"></i>
            </button>
             <button id="desktop-menu-toggle" class="w-10 h-10 rounded-full border items-center justify-center hover:bg-gray-100 hidden lg:flex"> {{-- Mostrar solo en lg+ --}}
                <i class="mdi mdi-menu text-xl"></i>
            </button>
        </div>

        {{-- Logo --}}
        <div class="absolute left-1/2 transform -translate-x-1/2">
             {{-- RECOMENDACIÓN: Usar accesor del modelo User --}}
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
                        {{-- RECOMENDACIÓN: Usar accesor del modelo User --}}
                        <div class="text-gray-800 font-semibold">{{ Auth::user()->name }}</div>
                        @php
                            // RECOMENDACIÓN: Mover a accesor User->formatted_role_name
                            $rolNombre = ucfirst(Auth::user()->rol ?? 'Usuario');
                        @endphp
                        <div class="text-xs text-gray-500">{{ $rolNombre }}</div>
                    </div>
                    @php
                         // RECOMENDACIÓN: Mover a accesor User->profile_photo_url
                        $foto = Auth::user()->foto ?? null;
                        $fotoPerfil = $foto && file_exists(public_path("uploads/$foto"))
                                        ? asset("uploads/$foto")
                                        : asset("assets/images/users/avatar-default.png");
                    @endphp
                    <img src="{{ $fotoPerfil }}" class="w-10 h-10 rounded-full object-cover border" alt="Avatar">
                </div>

                {{-- Menú Avatar --}}
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
    {{-- Clases iniciales: hidden en móvil, block en lg+. La clase 'sidebar-collapsed' en <html> controlará su estado colapsado en desktop --}}
    <aside id="sidebar"
        class="fixed top-16 left-0 bottom-0 w-64 bg-white border-r border-gray-300 p-4 z-40 transition-transform duration-300 ease-in-out transform hidden lg:block lg:translate-x-0">
        <h2 class="text-xl font-bold mb-6">Menú</h2>
        <nav class="flex flex-col gap-3 text-sm">
            {{-- RECOMENDACIÓN: Usar @can para autorización --}}
            <a href="{{ route('calendario.index') }}" class="hover:underline text-gray-800">📅 Calendario</a>
            <a href="{{ route('programaciones.index') }}" class="hover:underline text-gray-800">📦 Programaciones</a>
            <a href="{{ route('agenda.index') }}" class="hover:underline text-gray-800">🗓️ Agenda</a>
            <a href="{{ route('coordinaciones.index') }}" class="hover:underline text-gray-800">📍 Coordinaciones</a>
            <a href="{{ route('grupos.index') }}" class="hover:underline text-gray-800"> 👥 Grupos </a>
            <a href="{{ route('cursos.index') }}" class="hover:underline text-gray-800">📘 Cursos</a>
            <a href="{{ route('aulas.index') }}" class="hover:underline text-gray-800">🏫 Aulas</a>
            <a href="{{ route('instructores.index') }}" class="hover:underline text-gray-800">🧑‍🏫 Instructores</a>

             {{-- RECOMENDACIÓN: Usar @can('viewAdminSection') o similar --}}
            @if(auth()->user()?->rol === 'administrador')
                <a href="{{ route('users.index') }}" class="hover:underline text-gray-800">👤 Usuarios</a>
                <a href="{{ route('feriados.index') }}" class="hover:underline text-gray-800">📅 Días Feriados</a>
            @endif
        </nav>
    </aside>

    {{-- OVERLAY (Sólo para móvil) --}}
    <div id="sidebar-overlay"
        class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm z-30 hidden lg:hidden transition-opacity duration-300 ease-in-out opacity-0"> {{-- Inicialmente oculto y transparente --}}
    </div>

    {{-- CONTENIDO --}}
    {{-- Margen inicial para desktop (lg:ml-64). La clase 'sidebar-collapsed' en <html> lo quitará si es necesario --}}
    <main id="main-content"
        class="mt-16 p-6 min-h-[calc(100vh-8rem)] pb-20 transition-all duration-300 ease-in-out lg:ml-64">
        {{ $slot }}
    </main>

    {{-- FOOTER --}}
    <footer class="fixed bottom-0 left-0 right-0 bg-white text-center text-sm py-2 border-t z-10">
        2025 © CursosLaser | Desarrollado por De Gouveia José
    </footer>

    {{-- Scripts (RECOMENDACIÓN FUERTE: Mover a resources/js/layout.js e importar en app.js) --}}
    <script>
        // --- Elementos del DOM ---
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('main-content');
        const overlay = document.getElementById('sidebar-overlay');
        const mobileToggleBtn = document.getElementById('menu-toggle'); // Botón móvil
        const desktopToggleBtn = document.getElementById('desktop-menu-toggle'); // Botón desktop
        const avatarBtn = document.getElementById('avatar-btn');
        const avatarMenu = document.getElementById('avatar-menu');
        const fullscreenBtn = document.getElementById('btnFullscreen');
        const body = document.body;
        const htmlElement = document.documentElement; // Para fullscreen

        // --- Funciones Sidebar/Overlay ---
        const openMobileSidebar = () => {
            if (!sidebar || !overlay) return;
            sidebar.classList.remove('hidden');
            sidebar.classList.remove('-translate-x-full'); // Asegurar que esté visible
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10); // Iniciar transición de opacidad
            body.classList.add('overflow-hidden'); // Evitar scroll del body
        };

        const closeMobileSidebar = () => {
            if (!sidebar || !overlay) return;
            overlay.classList.add('opacity-0');
            sidebar.classList.add('-translate-x-full'); // Iniciar transición de salida
            body.classList.remove('overflow-hidden');
             // Ocultar completamente después de la transición
            setTimeout(() => {
                sidebar.classList.add('hidden');
                overlay.classList.add('hidden');
            }, 300); // Debe coincidir con duration-300
        };

        const toggleDesktopSidebar = () => {
             body.classList.toggle('sidebar-collapsed');
             // Opcional: Guardar preferencia en localStorage
             // localStorage.setItem('sidebarCollapsed', body.classList.contains('sidebar-collapsed'));
        };

        // --- Event Listeners ---

        // Toggle Móvil (< lg)
        mobileToggleBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sidebar?.classList.contains('hidden')) {
                openMobileSidebar();
            } else {
                closeMobileSidebar();
            }
        });

         // Toggle Desktop (>= lg)
         desktopToggleBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDesktopSidebar();
        });

        // Cerrar sidebar móvil con Overlay
        overlay?.addEventListener('click', closeMobileSidebar);

        // Cerrar sidebar móvil con tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && window.innerWidth < 1024 && !sidebar?.classList.contains('hidden')) {
                closeMobileSidebar();
            }
        });

        // --- Avatar Menu ---
        avatarBtn?.addEventListener('click', (e) => {
            e.stopPropagation(); // Previene que el listener del documento lo cierre inmediatamente
            avatarMenu?.classList.toggle('hidden');
        });

        // Cerrar menú avatar si se hace clic fuera
        document.addEventListener('click', (e) => {
            // Si el menú está visible y el clic NO fue dentro del botón O del menú
            if (avatarMenu && !avatarMenu.classList.contains('hidden') && !avatarBtn?.contains(e.target) && !avatarMenu.contains(e.target)) {
                 avatarMenu.classList.add('hidden');
            }
             // Cerrar sidebar móvil si se hace clic fuera de él (opcional)
             /*
             if (sidebar && !sidebar.classList.contains('hidden') && window.innerWidth < 1024 && !sidebar.contains(e.target) && !mobileToggleBtn?.contains(e.target)) {
                 closeMobileSidebar();
             }
             */
        });

        // --- Fullscreen ---
        fullscreenBtn?.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                htmlElement.requestFullscreen().catch(err => {
                    console.error(`Error al intentar activar pantalla completa: ${err.message} (${err.name})`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        });

        // --- Ajustes Iniciales y Responsivos ---

        // Función para ajustar estado en resize
        const handleResize = () => {
            const isLargeScreen = window.innerWidth >= 1024;

            if (isLargeScreen) {
                // Si estamos en pantalla grande, asegurar que el sidebar móvil/overlay estén cerrados
                closeMobileSidebar(); // Llama a la función que maneja clases y transiciones
                // El estado colapsado/expandido del sidebar desktop se mantiene por la clase en <body>
                 // Asegurar que el sidebar sea visible si no está colapsado
                 if (!body.classList.contains('sidebar-collapsed')) {
                     sidebar?.classList.remove('hidden');
                     sidebar?.classList.remove('-translate-x-full');
                 } else {
                    sidebar?.classList.add('-translate-x-full'); // Asegurar que esté oculto si está colapsado
                 }

            } else {
                // Si estamos en pantalla pequeña, asegurar que el sidebar desktop esté oculto (si no está activado manualmente)
                 // Si el sidebar no estaba explícitamente abierto en modo móvil, ocúltalo
                 if (!sidebar?.classList.contains('hidden')) {
                      // Decide si quieres ocultarlo siempre al pasar a móvil
                      // closeMobileSidebar();
                 }
                 // Asegurar que la clase de colapso del body no afecte en móvil
                 // body.classList.remove('sidebar-collapsed'); // O manejarlo con CSS (@media)
            }
        };

        // Listener de Resize
        window.addEventListener('resize', handleResize);

        // Listener de Carga Inicial
        window.addEventListener('DOMContentLoaded', () => {
             // Opcional: Restaurar estado del sidebar desktop desde localStorage
             // if (localStorage.getItem('sidebarCollapsed') === 'true') {
             //     body.classList.add('sidebar-collapsed');
             // } else {
             //     body.classList.remove('sidebar-collapsed');
             // }

            // Aplicar estado inicial basado en tamaño y clase del body
            handleResize(); // Llama a la función de resize para establecer el estado inicial correcto
        });

    </script>

</body>
</html>
