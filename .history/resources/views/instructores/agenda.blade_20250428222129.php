<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            🗓️ Bienvenido a tu agenda, {{ auth()->user()->name }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
        @if($programaciones->isEmpty())
            <div class="bg-white shadow sm:rounded-lg p-4 text-center">
                <p class="text-gray-600">No tienes cursos asignados aún.</p>
            </div>
        @else
            <div class="bg-white shadow sm:rounded-lg p-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Curso</th>
                            <th class="px-4 py-2 text-left">Grupo</th>
                            <th class="px-4 py-2 text-left">Fecha Inicio</th>
                            <th class="px-4 py-2 text-left">Fecha Fin</th>
                            <th class="px-4 py-2 text-left">Estado</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($programaciones as $programacion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $programacion->curso->nombre ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $programacion->grupo->nombre ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $programacion->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $programacion->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2">
                                    <span @class([
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $programacion->estado_confirmacion === 'confirmado',
                                        'bg-red-100 text-red-800' => $programacion->estado_confirmacion === 'rechazado',
                                        'bg-yellow-100 text-yellow-800' => is_null($programacion->estado_confirmacion) || $programacion->estado_confirmacion === 'pendiente',
                                    ])>
                                        {{ ucfirst($programacion->estado_confirmacion ?? 'pendiente') }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    @if ($programacion->estado_confirmacion !== 'confirmado')
                                        <form method="POST" action="{{ route('mi-agenda.confirmar', $programacion->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:underline text-xs font-semibold">Confirmar</button>
                                        </form>
                                    @else
                                        <span class="text-green-600 text-xs font-semibold">Confirmado</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Calendario --}}
        <div class="bg-white shadow sm:rounded-lg p-6 mt-8">
            <div id="instructor-agenda-calendar" class="h-96"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.instructorActualId = {{ auth()->user()->instructor->id }};
        </script>

        {{-- Este dialog es para mostrar detalles al hacer click en un evento --}}
        <dialog id="modalDetalle" class="rounded-lg shadow-xl p-0 w-full max-w-lg overflow-hidden">
            <div class="bg-white p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Detalles del Curso</h3>
                    <button onclick="document.getElementById('modalDetalle').close()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modalContent" class="text-sm text-gray-700 space-y-2"></div>
                <div class="mt-6 text-right">
                    <button onclick="document.getElementById('modalDetalle').close()" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Cerrar
                    </button>
                </div>
            </div>
        </dialog>

        {{-- Cargar el nuevo calendar-mi-agenda.js --}}
        <script type="module" src="{{ Vite::asset('resources/js/calendar-mi-agenda.js') }}"></script>
    @endpush
</x-app-layout>
