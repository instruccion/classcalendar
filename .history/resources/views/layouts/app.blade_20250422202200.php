<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ isSidebarOpen: localStorage.getItem('sidebarOpen') === 'true' }"
      x-bind:class="{ 'sidebar-collapsed': !isSidebarOpen }"
      x-init="$watch('isSidebarOpen', value => localStorage.setItem('sidebarOpen', value))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

     <!-- Icons MDI -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.3.67/css/materialdesignicons.min.css">

    <!-- Scripts and Styles (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Estilos para Sidebar Colapsable (Mover a app.css luego) --}}
    <style>
        :root {
            --sidebar-width: 16rem; /* w-64 */
            --sidebar-width-collapsed: 5rem; /* w-20 */
        }
        /* Transiciones */
        #sidebar, #main-content {
            transition: width 0.3s ease-in-out, margin-left 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
        #sidebar .sidebar-link-text, #sidebar .sidebar-title, #sidebar .sidebar-logo {
             transition: opacity 0.2s ease-in-out;
             white-space: nowrap; /* Evitar salto de línea al colapsar */
         }

        /* Estado Colapsado Desktop (lg+) */
        @media (min-width: 1024px) {
            .sidebar-collapsed #sidebar { width: var(--sidebar-width-collapsed); }
            .sidebar-collapsed #main-content { margin-left: var(--sidebar-width-collapsed); }
            .sidebar-collapsed #sidebar .sidebar-link-text,
            .sidebar-collapsed #sidebar .sidebar-title,
            .sidebar-collapsed #sidebar .sidebar-logo-text { /* Ocultar texto/título/logo-texto */
                opacity: 0; pointer-events: none; width: 0; display:none; /* Ocultar completamente */
            }
             .sidebar-collapsed #sidebar .sidebar-link svg { margin-left: auto; margin-right: auto; } /* Centrar icono */
             .sidebar-collapsed #sidebar .sidebar-link { justify-content: center; padding-left: 0.5rem; padding-right: 0.5rem;}
             /* Ajustar logo si tienes uno solo icono visible */
             .sidebar-collapsed #sidebar .sidebar-logo-container { justify-content: center; }
        }

        /* Estado Móvil (<lg) */
         @media (max-width: 1023px) {
            #main-content { margin-left: 0 !important; } /* Sin margen */
            #sidebar {
                transform: translateX(-100%); /* Oculto por defecto */
                width: var(--sidebar-width); /* Ancho completo al mostrar */
                z-index: 40; /* Asegurar que esté sobre el overlay */
             }
             #sidebar.show-mobile { transform: translateX(0); } /* Mostrar */
         }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex"> {{-- Cambiado a flex --}}

        {{-- ======================= --}}
        {{--      SIDEBAR            --}}
        {{-- ======================= --}}
        {{-- ======================= --}}
    {{--      SIDEBAR            --}}
    {{-- ======================= --}}
    <aside id="sidebar"
           class="fixed lg:relative top-0 left-0 bottom-0 mt-16 lg:mt-0 flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 pt-4 p-4 z-40 {{-- Añadido z-40 y padding top para móvil --}}
                  w-64 {{-- Ancho por defecto --}}
                  transform -translate-x-full lg:translate-x-0 {{-- Control inicial móvil/desktop --}}
                  transition-all duration-300 ease-in-out {{-- Transición --}}
                  overflow-y-auto {{-- Scroll si es necesario --}}
                  ">
           {{-- La clase 'sidebar-collapsed' en <html> reducirá el width en desktop --}}
           {{-- La clase 'show-mobile' (añadida por JS) lo mostrará en móvil --}}

        {{-- Contenedor del Logo (Opcional) --}}
        <div class="sidebar-logo-container flex items-center justify-center h-16 lg:h-auto mb-4 flex-shrink-0 px-2"> {{-- Añadido px-2 --}}
             <a href="{{ route('dashboard') }}" class="inline-block">
                  <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                  {{-- <span class="sidebar-logo-text font-semibold ml-2">Tu App</span> --}} {{-- Texto opcional del logo --}}
             </a>
        </div>

        {{-- Título Menú (Opcional) --}}
         <h2 class="sidebar-title text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2 mt-2">Menú Principal</h2>

        {{-- Navegación Principal --}}
        <nav class="flex-grow flex flex-col space-y-1"> {{-- flex-grow para empujar logout abajo --}}

            {{-- Enlaces usando el componente --}}
            <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="mdi-view-dashboard-outline">
                Dashboard
            </x-sidebar-link>
            <x-sidebar-link :href="route('calendario.index')" :active="request()->routeIs('calendario.index')" icon="mdi-calendar-month-outline">
                Calendario
            </x-sidebar-link>
             <x-sidebar-link :href="route('admin.programaciones.create')" :active="request()->routeIs('admin.programaciones.create') || request()->routeIs('admin.programaciones.edit') || request()->routeIs('admin.programaciones.bloque.*')" icon="mdi-calendar-plus"> {{-- Agrupamos rutas relacionadas --}}
                Programar Curso
            </x-sidebar-link>
             <x-sidebar-link :href="route('admin.programaciones.index')" :active="request()->routeIs('admin.programaciones.index') || request()->routeIs('admin.programaciones.show')" icon="mdi-format-list-bulleted-type">
                Programaciones
             </x-sidebar-link>
              <x-sidebar-link :href="route('admin.grupos.index')" :active="request()->routeIs('admin.grupos.*')" icon="mdi-account-group-outline">
                 Grupos
              </x-sidebar-link>
               <x-sidebar-link :href="route('admin.cursos.index')" :active="request()->routeIs('admin.cursos.*')" icon="mdi-book-open-page-variant-outline">
                  Cursos
               </x-sidebar-link>
               <x-sidebar-link :href="route('admin.aulas.index')" :active="request()->routeIs('admin.aulas.*')" icon="mdi-school-outline">
                  Aulas
               </x-sidebar-link>
               <x-sidebar-link :href="route('admin.instructores.index')" :active="request()->routeIs('admin.instructores.*')" icon="mdi-teach">
                  Instructores
               </x-sidebar-link>

            {{-- Sección Admin --}}
             @if(Auth::user()->esAdministrador())
                <hr class="my-3 border-gray-200 dark:border-gray-700">
                <h2 class="sidebar-title text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-2">Administración</h2>

                 <x-sidebar-link :href="route('admin.coordinaciones.index')" :active="request()->routeIs('admin.coordinaciones.*')" icon="mdi-map-marker-outline">
                    Coordinaciones
                </x-sidebar-link>
                 <x-sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" icon="mdi-account-cog-outline">
                    Usuarios
                </x-sidebar-link>
                <x-sidebar-link :href="route('admin.feriados.index')" :active="request()->routeIs('admin.feriados.*')" icon="mdi-calendar-remove-outline">
                    Días Feriados
                </x-sidebar-link>
                <x-sidebar-link :href="route('admin.auditorias.index')" :active="request()->routeIs('admin.auditorias.*')" icon="mdi-clipboard-list-outline">
                    Auditorías
                </x-sidebar-link>
             @endif

             {{-- Espaciador para empujar logout abajo --}}
             <div class="flex-grow"></div>

             {{-- Logout (Opcional en Sidebar) --}}
             <hr class="my-2 border-gray-200 dark:border-gray-700">
             <form method="POST" action="{{ route('logout') }}" x-ref="logoutForm">
                 @csrf
                 <x-sidebar-link href="#" @click.prevent="$refs.logoutForm.submit()" icon="mdi-logout">
                    {{ __('Log Out') }}
                 </x-sidebar-link>
             </form>
        </nav>
    </aside>

        {{-- Contenedor principal que incluye Header y Main Content --}}
        <div class="flex flex-col flex-1 w-full">

            {{-- ======================= --}}
            {{--      HEADER FIJO        --}}
            {{-- ======================= --}}
            <header class="sticky top-0 bg-white dark:bg-gray-800 shadow-md h-16 flex items-center justify-between px-4 z-30">
                 {{-- Botón Menú Izquierda (Controla Alpine y Sidebar Móvil) --}}
                 <div class="flex items-center">
                     <button @click="document.getElementById('sidebar').classList.toggle('show-mobile'); document.getElementById('sidebar-overlay').classList.toggle('hidden')"
                             class="p-2 rounded-md text-gray-600 hover:text-gray-900 focus:outline-none lg:hidden mr-2">
                         <i class="mdi mdi-menu text-2xl"></i>
                     </button>
                     <button @click="isSidebarOpen = !isSidebarOpen"
                             class="p-2 rounded-md text-gray-600 hover:text-gray-900 focus:outline-none hidden lg:inline-flex">
                         <i class="mdi mdi-menu text-2xl"></i>
                     </button>
                 </div>

                 {{-- Iconos Derecha (Notificaciones, Fullscreen, User Dropdown) --}}
                 <div class="flex items-center space-x-3">
                     {{-- Incluir aquí tu botón de notificaciones y fullscreen si los tenías --}}

                     {{-- User Dropdown (Tomado de Breeze navigation) --}}
                     <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->name }}</div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                                <form method="POST" action="{{ route('logout') }}"> @csrf <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link></form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                     {{-- User Dropdown para móvil (se maneja en el menú hamburguesa de navigation.blade.php si lo mantienes) --}}
                 </div>
            </header>

            {{-- ======================= --}}
            {{--      CONTENIDO PÁGINA   --}}
            {{-- ======================= --}}
            {{-- El margen izquierdo se aplica aquí y se ajusta con CSS --}}
            <main id="main-content" class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto {{-- Quitamos mt-16 porque header es sticky --}}
                           min-h-[calc(100vh-4rem)] {{-- Ajustar min-height por header fijo --}}
                           pb-10 {{-- Padding inferior para footer --}}
                           transition-all duration-300 ease-in-out
                           lg:ml-64"> {{-- Margen inicial desktop --}}

                 <!-- Page Heading -->
                 @if (isset($header))
                     <header class="mb-6">
                         <div class="max-w-7xl mx-auto">
                              {{ $header }}
                         </div>
                     </header>
                 @endif

                 <!-- Page Content -->
                 <div class="max-w-7xl mx-auto">
                      {{ $slot }}
                 </div>

                 {{-- Toasts --}}
                 @if(session('toast'))
                    {{-- ... tu código toast existente ... --}}
                 @endif
            </main>

        </div> {{-- Fin Flex Container Principal --}}

        {{-- OVERLAY para sidebar móvil --}}
        <div id="sidebar-overlay"
             @click="document.getElementById('sidebar').classList.remove('show-mobile'); $el.classList.add('hidden')"
             class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden">
        </div>

        {{-- Footer (Opcional, si lo quieres fijo) --}}
        {{-- <footer class="fixed bottom-0 left-0 right-0 bg-white ..."> ... </footer> --}}

    </div> {{-- Fin min-h-screen --}}
</body>
</html>
