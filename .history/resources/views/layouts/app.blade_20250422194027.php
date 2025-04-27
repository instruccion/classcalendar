<!DOCTYPE html>
{{-- Usamos Alpine para controlar el estado del sidebar --}}
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

    <!-- Icons (Material Design Icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.3.67/css/materialdesignicons.min.css">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Estilos específicos para el sidebar colapsable (mejor mover a app.css) --}}
    <style>
        /* --- Sidebar States --- */
        /* Expanded state is default defined by Tailwind classes */
        /* Collapsed state styles */
        .sidebar-collapsed #sidebar {
            width: theme('spacing.20'); /* w-20 */
        }
        .sidebar-collapsed #sidebar .sidebar-link-text {
            display: none;
        }
        .sidebar-collapsed #sidebar .sidebar-link svg {
            margin: auto; /* Centrar icono */
        }
        .sidebar-collapsed #sidebar .sidebar-logo {
             opacity: 0; /* Ocultar logo en modo colapsado */
             pointer-events: none;
        }
         .sidebar-collapsed #sidebar .sidebar-title {
            display: none; /* Ocultar título "Menú" */
        }
         .sidebar-collapsed #sidebar .sidebar-link {
             justify-content: center; /* Centrar icono horizontalmente */
             padding-left: theme('spacing.2'); /* Ajustar padding */
             padding-right: theme('spacing.2');
         }


        /* --- Main Content Margins --- */
        /* Default margin for expanded sidebar on large screens */
        @media (min-width: 1024px) {
            #main-content {
                margin-left: theme('spacing.64'); /* ml-64 */
            }
            /* Margin when sidebar is collapsed on large screens */
            .sidebar-collapsed #main-content {
                margin-left: theme('spacing.20') !important; /* ml-20, usar !important si es necesario */
            }
        }
         /* Ensure no margin on small screens */
         @media (max-width: 1023px) {
            #main-content {
                margin-left: 0 !important;
            }
            /* Ocultar sidebar por defecto en móvil (se mostrará con JS) */
             #sidebar {
                transform: translateX(-100%);
             }
             #sidebar.show-mobile { /* Clase para mostrar en móvil */
                 transform: translateX(0);
             }
         }

        /* --- Transitions --- */
        #sidebar, #main-content {
            transition: width 0.3s ease-in-out, margin-left 0.3s ease-in-out, transform 0.3s ease-in-out;
        }
         #sidebar .sidebar-link-text {
            transition: opacity 0.2s ease-in-out;
         }
          .sidebar-collapsed #sidebar .sidebar-link-text {
             opacity: 0;
             pointer-events: none; /* Evitar interacción con texto oculto */
         }
    </style>

</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

        {{-- ======================= --}}
        {{--      HEADER             --}}
        {{-- ======================= --}}
        <header class="fixed top-0 left-0 right-0 bg-white dark:bg-gray-800 shadow-md h-16 flex items-center justify-between px-4 z-30">
            {{-- Botones de menú (Izquierda) --}}
            <div class="flex items-center">
                 {{-- Botón para móvil (controla 'show-mobile' en sidebar) --}}
                 <button @click="document.getElementById('sidebar').classList.toggle('show-mobile'); document.getElementById('sidebar-overlay').classList.toggle('hidden')"
                        class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 lg:hidden">
                     <i class="mdi mdi-menu text-2xl"></i>
                 </button>
                 {{-- Botón para desktop (controla isSidebarOpen de Alpine) --}}
                 <button @click="isSidebarOpen = !isSidebarOpen"
                        class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 hidden lg:inline-flex">
                    <i class="mdi mdi-menu text-2xl"></i>
                </button>
            </div>

            {{-- Logo Central (Opcional) --}}
             <div class="absolute left-1/2 transform -translate-x-1/2">
                 <a href="{{ route('dashboard') }}"> {{-- Enlace al dashboard --}}
                     <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                 </a>
             </div>

            {{-- Iconos y Perfil (Derecha) --}}
            <div class="flex items-center space-x-3">
                {{-- Notificaciones (si las usas) --}}
                {{-- <button>...</button> --}}
                {{-- Pantalla Completa (si la usas) --}}
                {{-- <button>...</button> --}}

                {{-- Menú Desplegable de Usuario (de Breeze navigation.blade.php) --}}
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </header>

        {{-- ======================= --}}
        {{--      SIDEBAR            --}}
        {{-- ======================= --}}
        <aside id="sidebar"
               class="fixed top-0 left-0 bottom-0 mt-16 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 p-4 z-20 overflow-y-auto transition-all duration-300 ease-in-out transform -translate-x-full lg:translate-x-0">
               {{-- La clase 'sidebar-collapsed' en <html> reducirá el width en desktop --}}
               {{-- La clase 'show-mobile' (añadida por JS) lo mostrará en móvil --}}

            {{-- Logo Sidebar (Opcional) --}}
            <div class="mb-4 text-center sidebar-logo transition-opacity duration-200 ease-in-out">
                 <a href="{{ route('dashboard') }}">
                     {{-- Puedes poner un logo diferente o el mismo --}}
                      <x-application-logo class="inline-block h-8 w-auto fill-current text-gray-800 dark:text-gray-200" />
                 </a>
            </div>

            {{-- Título Menú (Opcional) --}}
             <h2 class="sidebar-title text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Menú Principal</h2>

            <nav class="flex flex-col space-y-1">
                {{-- Enlace de ejemplo (repetir para cada item) --}}
                <a href="{{ route('dashboard') }}"
                   class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                          {{ request()->routeIs('dashboard') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                   <i class="mdi mdi-view-dashboard-outline text-xl mr-3 flex-shrink-0"></i>
                   <span class="sidebar-link-text">Dashboard</span>
                </a>

                 <a href="{{ route('calendario.index') }}"
                    class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                           {{ request()->routeIs('calendario.index') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                    <i class="mdi mdi-calendar-month-outline text-xl mr-3 flex-shrink-0"></i>
                    <span class="sidebar-link-text">Calendario</span>
                 </a>

                 <a href="{{ route('admin.programaciones.create') }}"
                    class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                           {{ request()->routeIs('admin.programaciones.create') || request()->routeIs('admin.programaciones.edit') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                    <i class="mdi mdi-calendar-plus text-xl mr-3 flex-shrink-0"></i>
                    <span class="sidebar-link-text">Programar Curso</span>
                 </a>

                 <a href="{{ route('admin.programaciones.index') }}"
                 class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                        {{ request()->routeIs('admin.programaciones.index') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                    <i class="mdi mdi-format-list-bulleted-type text-xl mr-3 flex-shrink-0"></i>
                    <span class="sidebar-link-text">Programaciones</span>
                 </a>

                  <a href="{{ route('admin.grupos.index') }}"
                     class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                           {{ request()->routeIs('admin.grupos.*') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                     <i class="mdi mdi-account-group-outline text-xl mr-3 flex-shrink-0"></i>
                     <span class="sidebar-link-text">Grupos</span>
                  </a>

                   <a href="{{ route('admin.cursos.index') }}"
                      class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                           {{ request()->routeIs('admin.cursos.*') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                      <i class="mdi mdi-book-open-page-variant-outline text-xl mr-3 flex-shrink-0"></i>
                      <span class="sidebar-link-text">Cursos</span>
                   </a>

                   <a href="{{ route('admin.aulas.index') }}"
                      class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                           {{ request()->routeIs('admin.aulas.*') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                      <i class="mdi mdi-school-outline text-xl mr-3 flex-shrink-0"></i>
                      <span class="sidebar-link-text">Aulas</span>
                   </a>

                   <a href="{{ route('admin.instructores.index') }}"
                      class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                           {{ request()->routeIs('admin.instructores.*') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                      <i class="mdi mdi-teach text-xl mr-3 flex-shrink-0"></i>
                      <span class="sidebar-link-text">Instructores</span>
                   </a>

                {{-- Sección solo para Administradores --}}
                @if(Auth::user()->esAdministrador())
                    <hr class="my-3 border-gray-200 dark:border-gray-700">
                    <h2 class="sidebar-title text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-2">Administración</h2>

                     <a href="{{ route('admin.coordinaciones.index') }}"
                        class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                               {{ request()->routeIs('admin.coordinaciones.*') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                        <i class="mdi mdi-map-marker-outline text-xl mr-3 flex-shrink-0"></i>
                        <span class="sidebar-link-text">Coordinaciones</span>
                     </a>

                     <a href="{{ route('admin.users.index') }}"
                         class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                               {{ request()->routeIs('admin.users.*') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                         <i class="mdi mdi-account-cog-outline text-xl mr-3 flex-shrink-0"></i>
                         <span class="sidebar-link-text">Usuarios</span>
                     </a>

                    <a href="{{ route('admin.feriados.index') }}"
                       class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                              {{ request()->routeIs('admin.feriados.*') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                       <i class="mdi mdi-calendar-remove-outline text-xl mr-3 flex-shrink-0"></i>
                       <span class="sidebar-link-text">Días Feriados</span>
                    </a>

                    <a href="{{ route('admin.auditorias.index') }}"
                       class="sidebar-link flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition ease-in-out duration-150
                              {{ request()->routeIs('admin.auditorias.index') ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white' }}">
                       <i class="mdi mdi-clipboard-list-outline text-xl mr-3 flex-shrink-0"></i>
                       <span class="sidebar-link-text">Auditorías</span>
                    </a>
                @endif

            </nav>
        </aside>

        {{-- OVERLAY (Sólo para móvil) --}}
        <div id="sidebar-overlay"
             @click="document.getElementById('sidebar').classList.remove('show-mobile'); $el.classList.add('hidden')"
             class="fixed inset-0 bg-black bg-opacity-40 z-10 hidden lg:hidden">
        </div>

        {{-- ======================= --}}
        {{--      CONTENIDO          --}}
        {{-- ======================= --}}
        {{-- El margen izquierdo (ml-64 o ml-20) se controla con CSS y la clase 'sidebar-collapsed' en <html> --}}
        <main id="main-content" class="mt-16 p-4 sm:p-6 lg:p-8 min-h-[calc(100vh-8rem)] pb-16 lg:pb-10 transition-all duration-300 ease-in-out">
            {{-- Page Heading (si existe) --}}
            @if (isset($header))
                <header class="mb-6">
                    <div class="max-w-7xl mx-auto">
                         {{ $header }} {{-- El h2 ya viene del slot --}}
                    </div>
                </header>
            @endif

            {{-- Page Content --}}
            <div class="max-w-7xl mx-auto"> {{-- Contenedor opcional para el contenido principal --}}
                 {{ $slot }}
            </div>

            {{-- Toasts y Alertas (usando tu componente si existe o mostrándolos aquí) --}}
             @if(session('toast'))
                 {{-- Ejemplo de Toast con Alpine (necesitas tu componente x-toast o adaptar esto) --}}
                 <div x-data="{ show: true }"
                      x-show="show"
                      x-init="setTimeout(() => show = false, 4000)"
                      x-transition:enter="transition ease-out duration-300"
                      x-transition:enter-start="opacity-0 transform translate-y-2"
                      x-transition:enter-end="opacity-100 transform translate-y-0"
                      x-transition:leave="transition ease-in duration-200"
                      x-transition:leave-start="opacity-100 transform translate-y-0"
                      x-transition:leave-end="opacity-0 transform translate-y-2"
                      class="fixed bottom-5 right-5 px-4 py-2 rounded shadow-lg text-white z-50 {{ match(session('toast.type') ?? 'info') { 'success' => 'bg-green-600', 'error' => 'bg-red-600', 'warning' => 'bg-yellow-500', default => 'bg-blue-600' } }}"
                      role="alert">
                     {{ session('toast.message') }}
                 </div>
             @endif

        </main>

        {{-- FOOTER (Opcional, quitar si no lo necesitas fijo) --}}
         <footer class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 text-center text-xs py-2 border-t border-gray-200 dark:border-gray-700 z-10">
             {{ date('Y') }} © {{ config('app.name') }} | Desarrollado por De Gouveia José
         </footer>

    </div> {{-- Fin del div min-h-screen --}}

    {{-- Los scripts JS principales se cargan con @vite al principio del head --}}
    <script>
        // Listener global para cerrar modales con $dispatch('close-dialog', 'modalID')
        window.addEventListener('close-dialog', event => {
            const id = event.detail;
            const dialog = document.getElementById(id);
            if (dialog && typeof dialog.close === 'function') {
                dialog.close();
            } else {
                console.warn(`No se pudo cerrar el dialog con ID: ${id}`);
            }
        });
    </script>


</body>
</html>
