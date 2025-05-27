{{-- resources/views/instructores/agenda.blade.php --}}
<x-app-layout>

    <div class="bg-white shadow rounded p-6 mb-6">
        <h2 class="text-2xl font-bold text-center text-gray-800">
            Bienvenido a tu Agenda, {{ auth()->user()->name }}
        </h2>
    </div>



    <div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

        @if($programaciones->isEmpty())
            <div class="bg-white shadow sm:rounded-lg p-4 text-center">
                <p class="text-gray-600">No tienes cursos asignados aún.</p>
            </div>
        @else
            {{-- Tabla de cursos --}}
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
                                <td class="px-4 py-2 space-x-2">
                                    @if ($programacion->estado_confirmacion !== 'confirmado')
                                        <form method="POST" action="{{ route('mi-agenda.confirmar', $programacion->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:underline text-xs font-semibold">
                                                Confirmar
                                            </button>
                                        </form>
                                        <button
                                            type="button"
                                            onclick="declinarCurso({{ $programacion->id }})"
                                            class="text-red-600 hover:underline text-xs font-semibold"
                                        >
                                            Declinar
                                        </button>
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
            <div id="calendar" class="w-full min-h-[30rem] overflow-hidden"></div>
        </div>

    </div>

    {{-- Modal Declinar --}}
    <dialog id="modalDeclinar" class="rounded-lg shadow-xl p-0 w-full max-w-lg overflow-hidden">
        <form method="POST" action="" id="declinarForm" class="bg-white p-6">
            @csrf
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Motivo de rechazo</h3>
            <textarea name="motivo_rechazo" required class="w-full border rounded p-2 mb-4" placeholder="Explica brevemente el motivo..."></textarea>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="cerrarModalDeclinar()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white">Enviar</button>
            </div>
        </form>
    </dialog>


    @push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');

        const calendar = new window.FullCalendar.Calendar(calendarEl, {
            plugins: [window.dayGridPlugin, window.interactionPlugin],
            locale: window.esLocale,
            initialView: 'dayGridMonth',
            events: {!! $eventosJson !!},  // Aquí sí es seguro usar {!! !!}
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
        });

        calendar.render();
    });
</script>
@endpush



</x-app-layout>
