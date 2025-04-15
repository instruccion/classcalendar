{{-- SIDEBAR --}}
    {{-- Clases iniciales: hidden en móvil, block en lg+. La clase 'sidebar-collapsed' en <html> controlará su estado colapsado en desktop --}}
    <aside id="sidebar"
        class="fixed top-16 left-0 bottom-0 w-64 bg-white border-r border-gray-300 p-4 z-40 transition-transform duration-300 ease-in-out transform translate-x-0 hidden lg:block">
        {{-- Nota: He quitado lg:translate-x-0 ya que translate-x-0 es el estado por defecto y se maneja con la clase sidebar-collapsed --}}

        <h2 class="text-xl font-bold mb-6">Menú</h2>
        <nav class="flex flex-col gap-3 text-sm">
            {{-- Enlaces Comunes --}}
            <a href="{{ route('calendario.index') }}" class="flex items-center gap-2 hover:underline text-gray-800">
                <i class="mdi mdi-calendar-blank-outline w-5 text-center"></i> Calendario
            </a>
            <a href="{{ route('programaciones.index') }}" class="flex items-center gap-2 hover:underline text-gray-800">
                 <i class="mdi mdi-archive-outline w-5 text-center"></i> Programaciones
            </a>
            <a href="{{ route('agenda.index') }}" class="flex items-center gap-2 hover:underline text-gray-800">
                 <i class="mdi mdi-book-open-page-variant-outline w-5 text-center"></i> Agenda
            </a>
            <a href="{{ route('grupos.index') }}" class="flex items-center gap-2 hover:underline text-gray-800">
                 <i class="mdi mdi-account-group-outline w-5 text-center"></i> Grupos
            </a>
            <a href="{{ route('cursos.index') }}" class="flex items-center gap-2 hover:underline text-gray-800">
                 <i class="mdi mdi-book-outline w-5 text-center"></i> Cursos
            </a>
            <a href="{{ route('aulas.index') }}" class="flex items-center gap-2 hover:underline text-gray-800">
                 <i class="mdi mdi-school-outline w-5 text-center"></i> Aulas
            </a>
            <a href="{{ route('instructores.index') }}" class="flex items-center gap-2 hover:underline text-gray-800">
                 <i class="mdi mdi-account-tie-outline w-5 text-center"></i> Instructores
            </a>

             {{-- Bloque Administrativo Condicional --}}
             {{-- RECOMENDACIÓN: Cambiar por @can('viewAdminSection') o similar --}}
            @if(auth()->user()?->rol === 'administrador')

                {{-- Línea Decorativa Separadora --}}
                {{-- my-3 añade margen vertical, border-t crea la línea superior, border-gray-200 define el color --}}
                <hr class="my-3 border-t border-gray-200">

                {{-- Título Opcional para la Sección Admin --}}
                {{-- <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-1">Administración</h3> --}}

                {{-- Enlaces Administrativos --}}
                <a href="{{ route('coordinaciones.index') }}" class="flex items-center gap-2 hover:underline text-gray-800">
                    <i class="mdi mdi-map-marker-outline w-5 text-center"></i> Coordinaciones
                </a>
                <a href="{{ route('users.index') }}" class="flex items-center gap-2 hover:underline text-gray-800">
                    <i class="mdi mdi-account-circle-outline w-5 text-center"></i> Usuarios
                </a>
                <a href="{{ route('feriados.index') }}" class="flex items-center gap-2 hover:underline text-gray-800">
                     <i class="mdi mdi-calendar-star w-5 text-center"></i> Días Feriados
                </a>
                {{-- Asegúrate que esta ruta 'auditorias.index' exista --}}
                @if(Route::has('auditorias.index'))
                <a href="{{ route('auditorias.index') }}" class="flex items-center gap-2 hover:underline text-gray-800">
                    <i class="mdi mdi-clipboard-list-outline w-5 text-center"></i> Auditorías
                </a>
                @endif
            @endif
        </nav>
    </aside>
