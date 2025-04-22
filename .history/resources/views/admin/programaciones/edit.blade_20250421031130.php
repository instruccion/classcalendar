<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Programación
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto">

        {{-- SCRIPT DE ALPINE --}}
        <script>
            function programacionForm(config) {
                return {
                    startDate: config.fecha_inicio || '',
                    startTime: config.hora_inicio || '08:30',
                    endDate: config.fecha_fin || '',
                    endTime: config.hora_fin || '',
                    csrfToken: config.csrfToken,
                    isLoadingEndDate: false,
                    calculatedEndDateText: '',
                    showEndDateToast: false,

                    calculateEndDate() {
                        this.endDate = ''; this.endTime = '';
                        if (!this.startDate || !config.duracion_horas) return;

                        this.isLoadingEndDate = true;
                        fetch(config.ruta_calculo, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                inicio: this.startDate,
                                hora_inicio: this.startTime,
                                horas: config.duracion_horas
                            })
                        })
                        .then(res => res.ok ? res.json() : res.json().then(err => { throw new Error(err.error) }))
                        .then(data => {
                            this.endDate = data.fecha_fin;
                            this.endTime = data.hora_fin;
                            this.calculatedEndDateText = `${data.fecha_fin} ${data.hora_fin}`;
                            this.showEndDateToast = true;
                        })
                        .catch(error => alert('Error al calcular fecha fin: ' + error.message))
                        .finally(() => {
                            this.isLoadingEndDate = false;
                            setTimeout(() => this.showEndDateToast = false, 4000);
                        });
                    },

                    validateBeforeSubmit(event) {
                        let message = '';

                        if (!document.getElementById('aula_id').value) {
                            message += '⚠️ No se ha seleccionado un aula.\n';
                        }
                        if (!document.getElementById('instructor_id').value) {
                            message += '⚠️ No se ha seleccionado un instructor.\n';
                        }

                        if (message !== '') {
                            this.showToast(message, 'warning');
                        }

                        // Continuar siempre (no bloquea el submit)
                        event.target.submit();
                    },

                    showToast(message, type = 'info') {
                        const toast = document.createElement('div');
                        toast.className = `fixed top-5 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow-lg z-50 text-sm transition-opacity duration-300 ${
                            type === 'warning' ? 'bg-yellow-500 text-black' :
                            type === 'success' ? 'bg-green-600 text-white' :
                            type === 'error' ? 'bg-red-600 text-white' : 'bg-blue-500 text-white'
                        }`;
                        toast.textContent = message;
                        document.body.appendChild(toast);
                        setTimeout(() => toast.classList.add('opacity-0'), 3000);
                        setTimeout(() => toast.remove(), 3500);
                    }
                };
            }
        </script>

        {{-- FORMULARIO --}}
        <div class="bg-white p-6 rounded shadow-md"
             x-data="programacionForm({
                 fecha_inicio: '{{ $programacion->fecha_inicio->format('Y-m-d') }}',
                 hora_inicio: '{{ $programacion->hora_inicio->format('H:i') }}',
                 fecha_fin: '{{ $programacion->fecha_fin->format('Y-m-d') }}',
                 hora_fin: '{{ $programacion->hora_fin->format('H:i') }}',
                 csrfToken: '{{ csrf_token() }}',
                 ruta_calculo: '{{ route('admin.api.programaciones.calcularFechaFin') }}',
                 duracion_horas: {{ $programacion->curso->duracion_horas ?? 0 }}
             })">

            <form method="POST"
                  action="{{ route('admin.programaciones.update', $programacion) }}"
                  @submit.prevent="validateBeforeSubmit"
                  class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                @csrf
                @method('PUT')

                {{-- GRUPO --}}
                <div>
                    <label for="grupo_id" class="block font-semibold mb-1">Grupo <span class="text-red-500">*</span></label>
                    <select name="grupo_id" id="grupo_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un grupo...</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ $programacion->grupo_id == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin coordinación' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- CURSO --}}
                <div>
                    <label for="curso_id" class="block font-semibold mb-1">Curso <span class="text-red-500">*</span></label>
                    <select name="curso_id" id="curso_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un curso...</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" {{ $programacion->curso_id == $curso->id ? 'selected' : '' }}>
                                {{ $curso->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- BLOQUE --}}
                <div class="md:col-span-2 flex items-center gap-4">
                    <label for="bloque_codigo" class="font-semibold">Bloque</label>
                    <input type="text" name="bloque_codigo" id="bloque_codigo"
                           value="{{ $programacion->bloque_codigo }}"
                           class="border px-4 py-2 rounded w-full md:w-1/3">
                </div>

                {{-- FECHAS Y HORAS --}}
                <div>
                    <label for="fecha_inicio" class="block font-semibold mb-1">Fecha Inicio <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio"
                           x-model="startDate" @change="calculateEndDate"
                           class="w-full border px-4 py-2 rounded" required>
                </div>

                <div>
                    <label for="hora_inicio" class="block font-semibold mb-1">Hora Inicio <span class="text-red-500">*</span></label>
                    <input type="time" name="hora_inicio" id="hora_inicio"
                           x-model="startTime" @change="calculateEndDate"
                           class="w-full border px-4 py-2 rounded" required>
                </div>

                <div>
                    <label for="fecha_fin" class="block font-semibold mb-1">Fecha Fin</label>
                    <input type="date" name="fecha_fin" id="fecha_fin"
                           x-model="endDate"
                           class="w-full border px-4 py-2 rounded bg-gray-100" readonly>
                </div>

                <div>
                    <label for="hora_fin" class="block font-semibold mb-1">Hora Fin</label>
                    <input type="time" name="hora_fin" id="hora_fin"
                           x-model="endTime"
                           class="w-full border px-4 py-2 rounded bg-gray-100" readonly>
                </div>

                {{-- AULA --}}
                <div>
                    <label for="aula_id" class="block font-semibold mb-1">Aula</label>
                    <select name="aula_id" id="aula_id" class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Sin aula</option>
                        @foreach($aulas as $aula)
                            <option value="{{ $aula->id }}" {{ $programacion->aula_id == $aula->id ? 'selected' : '' }}>
                                {{ $aula->nombre }}{{ $aula->lugar ? ' – ' . $aula->lugar : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- INSTRUCTOR --}}
                <div>
                    <label for="instructor_id" class="block font-semibold mb-1">Instructor</label>
                    <select name="instructor_id" id="instructor_id" class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Sin instructor</option>
                        @foreach($instructores as $instructor)
                            <option value="{{ $instructor->id }}" {{ $programacion->instructor_id == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- BOTÓN --}}
                <div class="md:col-span-2 text-center mt-6">
                    <button type="submit" class="bg-[#00AF40] text-white px-6 py-2 rounded hover:bg-green-700">
                        Guardar Cambios
                    </button>
                </div>
            </form>

            {{-- TOAST --}}
            <div x-show="showEndDateToast"
                 x-transition
                 x-cloak
                 class="fixed bottom-5 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50"
                 x-init="$watch('showEndDateToast', val => { if (val) setTimeout(() => showEndDateToast = false, 3500) })">
                📅 Fecha fin estimada: <span x-text="calculatedEndDateText"></span>
            </div>
        </div>
    </div>
</x-app-layout>
