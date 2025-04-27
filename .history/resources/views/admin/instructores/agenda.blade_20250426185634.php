{{-- resources/views/admin/instructores/agenda.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            📅 Agenda de {{ $selectedInstructor ? $selectedInstructor->nombre : 'Instructores' }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8"> {{-- Añadido padding responsivo --}}
        {{-- Selector de Instructor --}}
        <div class="bg-white shadow sm:rounded-lg p-4"> {{-- Envuelto en card --}}
            <form method="GET" action="{{ route('admin.instructores.agenda') }}" class="max-w-md">
                <label for="instructor_id" class="block font-medium text-sm text-gray-700 mb-1">Selecciona un instructor:</label>
                <select name="instructor_id" id="instructor_id" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">-- Elegir instructor --</option>
                    @foreach($instructores as $inst)
                        <option value="{{ $inst->id }}" @selected(request('instructor_id') == $inst->id)> {{-- Usar @selected --}}
                            {{ $inst->nombre }}
                        </option>
                    @endforeach
                </select>
                 {{-- Añadir botón para limpiar selección si se desea --}}
                 @if(request('instructor_id'))
                    <a href="{{ route('admin.instructores.agenda') }}" class="text-sm text-gray-600 hover:text-gray-900 mt-1 inline-block">Limpiar selección</a>
                 @endif
            </form>
        </div>

        {{-- Tabla de Programaciones (Solo si hay instructor seleccionado) --}}
        @if($instructor_id && $programaciones->count())
            <div class="bg-white shadow sm:rounded-lg p-4 overflow-x-auto">
                <h3 class="font-semibold text-lg mb-3 text-gray-800">Cursos asignados a {{ $selectedInstructor->nombre }}</h3>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider"> {{-- Estilo thead --}}
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left">Curso</th>
                            <th scope="col" class="px-4 py-2 text-left">Grupo</th>
                            <th scope="col" class="px-4 py-2 text-left">Inicio</th>
                            <th scope="col" class="px-4 py-2 text-left">Fin</th>
                            <th scope="col" class="px-4 py-2 text-left">Horario</th>
                            <th scope="col" class="px-4 py-2 text-left">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200"> {{-- Estilo tbody --}}
                        @foreach ($programaciones as $p)
                            <tr class="hover:bg-gray-50"> {{-- Hover effect --}}
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->curso->nombre ?? '—' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->grupo->nombre ?? '—' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->fecha_inicio?->format('d/m/Y') ?? 'N/A' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->fecha_fin?->format('d/m/Y') ?? 'N/A' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    @php
                                        $horaInicio = ($p->hora_inicio && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $p->hora_inicio)) ? substr($p->hora_inicio, 0, 5) : '—';
                                        $horaFin = ($p->hora_fin && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $p->hora_fin)) ? substr($p->hora_fin, 0, 5) : '—';
                                    @endphp
                                    {{ $horaInicio }} - {{ $horaFin }}
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
        @elseif($instructor_id)
             <div class="bg-white shadow sm:rounded-lg p-4">
                 <p class="text-center text-gray-500">No hay cursos asignados para {{ $selectedInstructor->nombre }}.</p>
             </div>
        @else
             <div class="bg-white shadow sm:rounded-lg p-4">
                 <p class="text-center text-gray-500">Selecciona un instructor para ver su agenda.</p>
             </div>
        @endif

        {{-- Calendario (Solo si hay instructor seleccionado) --}}
        @if($instructor_id)
            <div class="bg-white shadow sm:rounded-lg p-6">
                {{-- ========================================== --}}
                {{-- AQUÍ: ID ÚNICO para este calendario        --}}
                {{-- Así no choca con el script global calendario.js --}}
                {{-- ========================================== --}}
                <div id='instructor-agenda-calendar'></div>
            </div>
        @endif
    </div>

    {{-- Modal de Detalles (usando <dialog>) --}}
    <dialog id="modalDetalle" class="rounded-lg shadow-xl p-0 w-full max-w-lg overflow-hidden">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center mb-4">
                 <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Detalles del Curso</h3>
                 <button onclick="document.getElementById('modalDetalle').close()" class="text-gray-400 hover:text-gray-600">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>
            </div>

            <div id="modalContent" class="text-sm text-gray-700 space-y-2">
                {{-- El contenido se inyectará aquí --}}
                <p>Cargando...</p>
            </div>
            <div class="mt-6 text-right">
                <button onclick="document.getElementById('modalDetalle').close()" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cerrar
                </button>
            </div>
        </div>

    </dialog>

    @push('scripts')
    @vite(['resources/js/calendar-instructor.js'])

@endpush


</x-app-layout>
