<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            📅 Bienvenido a tu agenda, {{ auth()->user()->name }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

        {{-- Tabla de cursos asignados --}}
        @if($programaciones->isEmpty())
            <div class="bg-white shadow sm:rounded-lg p-4 text-center text-gray-500">
                No tienes cursos asignados aún.
            </div>
        @else
            <div class="bg-white shadow sm:rounded-lg p-4 overflow-x-auto">
                <h3 class="font-semibold text-lg mb-3 text-gray-800">Cursos asignados a {{ $instructor->nombre }}</h3>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2 text-left">Curso</th>
                            <th class="px-4 py-2 text-left">Grupo</th>
                            <th class="px-4 py-2 text-left">Inicio</th>
                            <th class="px-4 py-2 text-left">Fin</th>
                            <th class="px-4 py-2 text-left">Horario</th>
                            <th class="px-4 py-2 text-left">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($programaciones as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->curso->nombre ?? '—' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->grupo->nombre ?? '—' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    @if($p->hora_inicio && $p->hora_fin)
                                        {{ substr($p->hora_inicio, 0, 5) }} - {{ substr($p->hora_fin, 0, 5) }}
                                    @else
                                        — —
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <span @class([
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $p->estado_confirmacion === 'confirmado',
                                        'bg-red-100 text-red-800' => $p->estado_confirmacion === 'rechazado',
                                        'bg-yellow-100 text-yellow-800' => $p->estado_confirmacion !== 'confirmado' && $p->estado_confirmacion !== 'rechazado',
                                    ])>
                                        {{ ucfirst($p->estado_confirmacion ?? 'pendiente') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Calendario de agenda --}}
        <div class="bg-white shadow sm:rounded-lg p-6">
            <div id="instructor-agenda-calendar" class="h-96"></div>
        </div>
    </div>

    {{-- Modal de detalles --}}
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
            <div id="modalContent" class="text-sm text-gray-700 space-y-2">Cargando...</div>
            <div class="mt-6 text-right">
                <button onclick="document.getElementById('modalDetalle').close()" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                    Cerrar
                </button>
            </div>
        </div>
    </dialog>

    @push('scripts')
        <script>
            window.instructorActualId = {{ $instructor->id }};
        </script>
        @vite(['resources/js/calendar-mi-agenda.js'])
    @endpush
</x-app-layout>
