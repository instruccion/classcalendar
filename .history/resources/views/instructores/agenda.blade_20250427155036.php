{{-- resources/views/instructores/agenda.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            🗓️ Mi Agenda
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
