<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Bloque – {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coord.' }}) <span class="text-gray-600">[{{ $bloque_codigo ?? 'sin código' }}]</span>
        </h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
    function ordenarBloque(config) {
        return {
            cursos: config.cursosIniciales || [],
            feriados: new Set(config.feriados || []),
            fechaInicioBloque: config.fechaInicio || '',
            horaInicioBloque: config.horaInicio || '08:30',
            aulaId: config.aulaId || '',
            instructorId: config.instructorId || '',
            grupoId: config.grupoId,
            bloqueCodigo: config.bloqueCodigo,
            rutaUpdate: config.rutaUpdate,
            fechasCalculadas: false,

            init() {
                this.$nextTick(() => {
                    Sortable.create(this.$refs.sortableList, {
                        animation: 150,
                        handle: '.cursor-move',
                        onEnd: evt => {
                            const [moved] = this.cursos.splice(evt.oldIndex, 1);
                            this.cursos.splice(evt.newIndex, 0, moved);
                            this.cursos.forEach(c => Object.assign(c, { modificado: false }));
                            this.fechasCalculadas = false;
                        }
                    });
                });
            },

            calcular() {
                if (!this.fechaInicioBloque || !this.horaInicioBloque) {
                    alert('Debe seleccionar fecha y hora de inicio.');
                    return;
                }

                const feriados = this.feriados;
                const cursorInicio = new Date(`${this.fechaInicioBloque}T${this.horaInicioBloque}`);
                let cursor = cursorInicio.getTime();

                const horarios = {
                    manana: { inicio: 8 * 60 + 30, fin: 12 * 60 },
                    tarde: { inicio: 13 * 60, fin: 17 * 60 },
                    minutosHoraAcademica: 50
                };

                const pasarAlDiaSiguiente = () => {
                    const d = new Date(cursor);
                    d.setDate(d.getDate() + 1);
                    d.setHours(8, 30, 0, 0);
                    return d.getTime();
                };

                const esFeriado = fecha => {
                    const ymd = fecha.toISOString().slice(0, 10);
                    return [0, 6].includes(fecha.getDay()) || feriados.has(ymd);
                };

                const ajustarCursor = () => {
                    let date = new Date(cursor);
                    while (esFeriado(date)) {
                        cursor = pasarAlDiaSiguiente();
                        date = new Date(cursor);
                    }
                    const min = date.getHours() * 60 + date.getMinutes();
                    if (min < horarios.manana.inicio) date.setHours(8, 30);
                    if (min >= 12 * 60 && min < 13 * 60) date.setHours(13, 0);
                    if (min >= horarios.tarde.fin) cursor = pasarAlDiaSiguiente();
                    cursor = date.getTime();
                };

                this.cursos.forEach((curso, index) => {
                    ajustarCursor();
                    const inicio = new Date(cursor);
                    curso.fecha_inicio = inicio.toISOString().slice(0, 10);
                    curso.hora_inicio = inicio.toTimeString().slice(0, 5);

                    let minutosRestantes = curso.duracion_horas * horarios.minutosHoraAcademica;
                    while (minutosRestantes > 0) {
                        ajustarCursor();
                        const actual = new Date(cursor);
                        const min = actual.getHours() * 60 + actual.getMinutes();

                        let maxFin = 0;
                        if (min >= horarios.manana.inicio && min < horarios.manana.fin) {
                            maxFin = horarios.manana.fin;
                        } else if (min >= horarios.tarde.inicio && min < horarios.tarde.fin) {
                            maxFin = horarios.tarde.fin;
                        } else {
                            cursor = pasarAlDiaSiguiente();
                            continue;
                        }

                        const minutosDisponibles = maxFin - min;
                        const aUsar = Math.min(minutosRestantes, minutosDisponibles);
                        cursor += aUsar * 60000;
                        minutosRestantes -= aUsar;
                    }

                    const fin = new Date(cursor);
                    curso.fecha_fin = fin.toISOString().slice(0, 10);
                    curso.hora_fin = fin.toTimeString().slice(0, 5);

                    if (fin.getHours() >= 15) {
                        cursor = pasarAlDiaSiguiente();
                    }

                    curso.modificado = false;
                });

                this.fechasCalculadas = true;
            },

            guardar() {
                this.$refs.form.submit();
            }
        }
    }
    </script>

    <div class="py-6 max-w-5xl mx-auto"
         x-data="ordenarBloque({
            cursosIniciales: {{ Js::from($cursosParaVista) }},
            feriados: {{ Js::from($feriados) }},
            fechaInicio: '{{ $fechaInicioActual }}',
            horaInicio: '{{ $horaInicioActual }}',
            aulaId: '{{ $aulaActualId }}',
            instructorId: '{{ $instructorActualId }}',
            grupoId: {{ $grupo->id }},
            bloqueCodigo: '{{ $bloque_codigo }}',
            rutaUpdate: '{{ route('admin.programaciones.bloque.update', [$grupo->id, $bloque_codigo]) }}'
         })" x-init="init()">

        <form x-ref="form" method="POST" :action="rutaUpdate">
            @csrf
            <div class="bg-white rounded shadow p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm">Fecha de Inicio</label>
                        <input type="date" class="w-full border rounded py-1 px-2" x-model="fechaInicioBloque" required>
                    </div>
                    <div>
                        <label class="text-sm">Hora de Inicio</label>
                        <input type="time" class="w-full border rounded py-1 px-2" x-model="horaInicioBloque" required>
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="calcular"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">
                            Calcular Horarios
                        </button>
                    </div>
                </div>

                <ul x-ref="sortableList" class="mt-4 space-y-4">
                    <template x-for="(curso, index) in cursos" :key="curso.id">
                        <li class="border rounded p-4 bg-gray-50 shadow-sm cursor-move">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-indigo-700" x-text="curso.nombre"></span>
                                <span class="text-xs text-gray-600">Duración: <span x-text="curso.duracion_horas"></span> h</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">
                                <div>
                                    <label>Inicio</label>
                                    <input type="date" class="w-full border rounded px-2 py-1" :name="`cursos[${index}][fecha_inicio]`" x-model="curso.fecha_inicio" required>
                                </div>
                                <div>
                                    <label>Hora Ini.</label>
                                    <input type="time" class="w-full border rounded px-2 py-1" :name="`cursos[${index}][hora_inicio]`" x-model="curso.hora_inicio" required>
                                </div>
                                <div>
                                    <label>Fin</label>
                                    <input type="date" class="w-full border rounded px-2 py-1" :name="`cursos[${index}][fecha_fin]`" x-model="curso.fecha_fin" required>
                                </div>
                                <div>
                                    <label>Hora Fin</label>
                                    <input type="time" class="w-full border rounded px-2 py-1" :name="`cursos[${index}][hora_fin]`" x-model="curso.hora_fin" required>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>

                <div class="text-center mt-6">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow">
                        Guardar Cambios del Bloque
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
