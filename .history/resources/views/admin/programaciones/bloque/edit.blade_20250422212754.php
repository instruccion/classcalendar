<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Bloque – {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coordinación' }})
        </h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
        function ordenarBloque(config) {
            return {
                cursos: config.cursosIniciales || [],
                feriados: new Set(config.feriados || []),
                grupoId: config.grupoId,
                bloqueCodigo: config.bloqueCodigoOriginal,
                rutaUpdateBloque: config.rutaUpdateBloque,
                fechaInicioBloque: config.fechaInicioBloque || '',
                fechasCalculadas: false,
                cursosDisponibles: config.cursosDisponibles || [],
                tipoSeleccionado: 'Todos',
                cursosFiltrados() {
                    if (this.tipoSeleccionado === 'Todos') return this.cursosDisponibles;
                    return this.cursosDisponibles.filter(c => {
                        return (c.tipo || '').toLowerCase() === this.tipoSeleccionado.toLowerCase();
                    });
                },
                init() {
                    this.$nextTick(() => {
                        const sortableList = this.$refs.sortableList;
                        if (sortableList && typeof Sortable !== 'undefined') {
                            Sortable.create(sortableList, {
                                animation: 150,
                                handle: '.cursor-grab',
                                ghostClass: 'bg-blue-100',
                                onEnd: (evt) => {
                                    const [movedItem] = this.cursos.splice(evt.oldIndex, 1);
                                    this.cursos.splice(evt.newIndex, 0, movedItem);
                                    this.fechasCalculadas = false;
                                }
                            });
                        }
                    });
                },
                agregarCurso(id) {
                    const curso = this.cursosDisponibles.find(c => c.id == id);
                    if (curso && !this.cursos.some(c => c.id == id)) {
                        this.cursos.push({
                            id: curso.id,
                            nombre: curso.nombre,
                            tipo: curso.tipo,
                            duracion_horas: curso.duracion_horas,
                            fecha_inicio: '',
                            hora_inicio: '',
                            fecha_fin: '',
                            hora_fin: '',
                            programacion_id: null,
                            modificado: true
                        });
                    }
                },
                calcularHorariosBloque() {
                    if (!this.fechaInicioBloque) {
                        alert('Seleccione una fecha de inicio para el bloque.');
                        return;
                    }
                    if (this.cursos.length === 0) {
                        alert('No hay cursos para calcular.');
                        return;
                    }

                    const MINUTOS_HORA_ACADEMICA = 50;
                    const horarioMananaInicio = 8 * 60 + 30;
                    const horarioMananaFin = 12 * 60;
                    const horarioTardeInicio = 13 * 60;
                    const horarioTardeFin = 17 * 60;
                    const feriados = this.feriados;

                    let cursor = new Date(`${this.fechaInicioBloque}T08:30:00`).getTime();

                    const formatDate = date => {
                        return date.toISOString().split('T')[0];
                    };

                    const formatTime = date => {
                        return date.toTimeString().slice(0, 5);
                    };

                    const esFeriado = (fecha) => feriados.has(formatDate(fecha));
                    const esFinDeSemana = (fecha) => [0, 6].includes(fecha.getDay());

                    const pasarAlDiaHabil = (timestamp) => {
                        let f = new Date(timestamp);
                        do {
                            f.setDate(f.getDate() + 1);
                        } while (esFinDeSemana(f) || esFeriado(f));
                        f.setHours(8, 30, 0, 0);
                        return f.getTime();
                    };

                    const ajustarCursor = (timestamp) => {
                        let f = new Date(timestamp);
                        if (esFinDeSemana(f) || esFeriado(f)) {
                            return pasarAlDiaHabil(timestamp);
                        }
                        const min = f.getHours() * 60 + f.getMinutes();
                        if (min < horarioMananaInicio) {
                            f.setHours(8, 30, 0, 0);
                        } else if (min >= 12 * 60 && min < 13 * 60) {
                            f.setHours(13, 0, 0, 0);
                        } else if (min >= 17 * 60) {
                            return pasarAlDiaHabil(timestamp);
                        }
                        return f.getTime();
                    };

                    this.cursos.forEach((curso, index) => {
                        let minutosPendientes = curso.duracion_horas * MINUTOS_HORA_ACADEMICA;
                        cursor = ajustarCursor(cursor);

                        const inicio = new Date(cursor);
                        curso.fecha_inicio = formatDate(inicio);
                        curso.hora_inicio = formatTime(inicio);

                        while (minutosPendientes > 0) {
                            cursor = ajustarCursor(cursor);
                            const f = new Date(cursor);
                            const minutoActual = f.getHours() * 60 + f.getMinutes();
                            let disponible = 0;

                            if (minutoActual < horarioMananaFin) {
                                disponible = horarioMananaFin - Math.max(minutoActual, horarioMananaInicio);
                            } else if (minutoActual < horarioTardeFin) {
                                if (minutoActual < horarioTardeInicio) {
                                    disponible = horarioTardeFin - horarioTardeInicio;
                                    f.setHours(13, 0, 0, 0);
                                    cursor = f.getTime();
                                } else {
                                    disponible = horarioTardeFin - minutoActual;
                                }
                            }

                            let usar = Math.min(disponible, minutosPendientes);
                            cursor += usar * 60000;
                            minutosPendientes -= usar;

                            if (usar <= 0) {
                                cursor = pasarAlDiaHabil(cursor);
                            }
                        }

                        const fin = new Date(cursor);
                        curso.fecha_fin = formatDate(fin);
                        curso.hora_fin = formatTime(fin);

                        if (fin.getHours() >= 15 && index < this.cursos.length - 1) {
                            cursor = pasarAlDiaHabil(cursor);
                        }
                    });

                    this.fechasCalculadas = true;
                },
                submitForm() {
                    this.$refs.formGuardarBloque.submit();
                }
            }
        }
    </script>

    <div class="py-6 max-w-6xl mx-auto"
         x-data="ordenarBloque({
             cursosIniciales: {{ Js::from($cursosParaVista) }},
             feriados: {{ Js::from($feriados) }},
             grupoId: {{ $grupo->id }},
             bloqueCodigoOriginal: '{{ $bloque_codigo ?? '_sin_codigo_' }}',
             rutaUpdateBloque: '{{ route('admin.programaciones.bloque.update', ['grupo' => $grupo->id, 'bloque_codigo' => $bloque_codigo ?? '_sin_codigo_']) }}',
             cursosDisponibles: {{ Js::from($cursosDisponibles ?? []) }}
         })" x-init="init()">

        <div class="bg-white p-6 rounded shadow-md">
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                     class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded shadow">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <form x-ref="formGuardarBloque" method="POST" :action="rutaUpdateBloque">
                @csrf
                @method('PUT')
                <input type="hidden" name="grupo_id" :value="grupoId">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Código del Bloque</label>
                        <input type="text" name="bloque_codigo" x-model="bloqueCodigo"
                               class="w-full border px-3 py-2 rounded" placeholder="Ej: BLOQ-01">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Inicio Primer Curso</label>
                        <input type="date" x-model="fechaInicioBloque"
                               class="w-full border px-3 py-2 rounded">
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="calcularHorariosBloque"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow w-full">
                            Recalcular Fechas
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 items-end">
                    <div class="w-full max-w-[14rem]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Curso</label>
                        <select x-model="tipoSeleccionado" class="w-full border rounded px-3 py-2">
                            <option value="Todos">Todos</option>
                            <option value="Inicial">Inicial</option>
                            <option value="Recurrente">Periódico</option>
                            <option value="General">General</option>
                            <option value="Específico">Específico</option>
                            <option value="OJT">OJT</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Agregar Curso al Bloque</label>
                        <select @change="agregarCurso($event.target.value); $event.target.value = ''"
                                class="w-full border rounded px-3 py-2">
                            <option value="">Seleccione un curso...</option>
                            <template x-for="c in cursosFiltrados()" :key="c.id">
                                <option :value="c.id" x-text="c.nombre + ' (' + (c.tipo || '-') + ')'" :disabled="cursos.some(cc => cc.id === c.id)"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <ul class="space-y-4" x-ref="sortableList">
                    <template x-for="(curso, index) in cursos" :key="curso.id + '-' + index">
                        <li class="border rounded p-4 bg-gray-50 shadow-sm cursor-grab">
                            <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">
                            <input type="hidden" :name="`cursos[${index}][programacion_id]`" :value="curso.programacion_id">
                            <input type="hidden" :name="`cursos[${index}][modificado]`" :value="curso.modificado ? '1' : '0'">

                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <strong x-text="curso.nombre" class="text-blue-700"></strong>
                                    <span class="text-xs text-gray-500 ml-2" x-text="`(${curso.duracion_horas}h)`"></span>
                                    <span class="text-xs text-gray-500 ml-2 italic" x-text="curso.tipo ? curso.tipo : '-' "></span>
                                </div>
                                <button type="button" @click="cursos.splice(index, 1)"
                                        class="text-red-600 text-xs hover:underline">
                                    Eliminar
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="text-sm">Fecha Inicio</label>
                                    <input type="date" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][fecha_inicio]`" x-model="curso.fecha_inicio">
                                </div>
                                <div>
                                    <label class="text-sm">Hora Inicio</label>
                                    <input type="time" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][hora_inicio]`" x-model="curso.hora_inicio">
                                </div>
                                <div>
                                    <label class="text-sm">Fecha Fin</label>
                                    <input type="date" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][fecha_fin]`" x-model="curso.fecha_fin">
                                </div>
                                <div>
                                    <label class="text-sm">Hora Fin</label>
                                    <input type="time" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][hora_fin]`" x-model="curso.hora_fin">
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="text-sm font-medium text-gray-700">Pertenece al Bloque:</label>
                                <span class="text-sm font-semibold text-indigo-600 underline ml-1" x-text="bloqueCodigo || '—'"></span>
                            </div>
                        </li>
                    </template>
                </ul>

                <div class="mt-6 text-center">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

    <div class="py-6 max-w-6xl mx-auto"
         x-data="ordenarBloque({
             cursosIniciales: {{ Js::from($cursosParaVista) }},
             feriados: {{ Js::from($feriados) }},
             grupoId: {{ $grupo->id }},
             bloqueCodigoOriginal: '{{ $bloque_codigo ?? '_sin_codigo_' }}',
             rutaUpdateBloque: '{{ route('admin.programaciones.bloque.update', ['grupo' => $grupo->id, 'bloque_codigo' => $bloque_codigo ?? '_sin_codigo_']) }}',
             cursosDisponibles: {{ Js::from($cursosDisponibles ?? []) }}
         })" x-init="init()">

        <div class="bg-white p-6 rounded shadow-md">
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                     class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded shadow">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <form x-ref="formGuardarBloque" method="POST" :action="rutaUpdateBloque">
                @csrf
                @method('PUT')
                <input type="hidden" name="grupo_id" :value="grupoId">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Código del Bloque</label>
                        <input type="text" name="bloque_codigo" x-model="bloqueCodigo"
                               class="w-full border px-3 py-2 rounded" placeholder="Ej: BLOQ-01">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Inicio Primer Curso</label>
                        <input type="date" x-model="fechaInicioBloque"
                               class="w-full border px-3 py-2 rounded">
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="calcularHorariosBloque"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow w-full">
                            Recalcular Fechas
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 items-end">
                    <div class="w-full max-w-[14rem]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Curso</label>
                        <select x-model="tipoSeleccionado" class="w-full border rounded px-3 py-2">
                            <option value="Todos">Todos</option>
                            <option value="Inicial">Inicial</option>
                            <option value="Recurrente">Periódico</option>
                            <option value="General">General</option>
                            <option value="Específico">Específico</option>
                            <option value="OJT">OJT</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Agregar Curso al Bloque</label>
                        <select @change="agregarCurso($event.target.value); $event.target.value = ''"
                                class="w-full border rounded px-3 py-2">
                            <option value="">Seleccione un curso...</option>
                            <template x-for="c in cursosFiltrados()" :key="c.id">
                                <option :value="c.id" x-text="c.nombre + ' (' + (c.tipo || '-') + ')'" :disabled="cursos.some(cc => cc.id === c.id)"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <ul class="space-y-4" x-ref="sortableList">
                    <template x-for="(curso, index) in cursos" :key="curso.id + '-' + index">
                        <li class="border rounded p-4 bg-gray-50 shadow-sm cursor-grab">
                            <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">
                            <input type="hidden" :name="`cursos[${index}][programacion_id]`" :value="curso.programacion_id">
                            <input type="hidden" :name="`cursos[${index}][modificado]`" :value="curso.modificado ? '1' : '0'">

                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <strong x-text="curso.nombre" class="text-blue-700"></strong>
                                    <span class="text-xs text-gray-500 ml-2" x-text="`(${curso.duracion_horas}h)`"></span>
                                    <span class="text-xs text-gray-500 ml-2 italic" x-text="curso.tipo ? curso.tipo : '-' "></span>
                                </div>
                                <button type="button" @click="cursos.splice(index, 1)"
                                        class="text-red-600 text-xs hover:underline">
                                    Eliminar
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="text-sm">Fecha Inicio</label>
                                    <input type="date" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][fecha_inicio]`" x-model="curso.fecha_inicio">
                                </div>
                                <div>
                                    <label class="text-sm">Hora Inicio</label>
                                    <input type="time" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][hora_inicio]`" x-model="curso.hora_inicio">
                                </div>
                                <div>
                                    <label class="text-sm">Fecha Fin</label>
                                    <input type="date" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][fecha_fin]`" x-model="curso.fecha_fin">
                                </div>
                                <div>
                                    <label class="text-sm">Hora Fin</label>
                                    <input type="time" class="w-full border px-2 py-1 rounded text-sm"
                                           :name="`cursos[${index}][hora_fin]`" x-model="curso.hora_fin">
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="text-sm font-medium text-gray-700">Pertenece al Bloque:</label>
                                <span class="text-sm font-semibold text-indigo-600 underline ml-1" x-text="bloqueCodigo || '—'"></span>
                            </div>
                        </li>
                    </template>
                </ul>

                <div class="mt-6 text-center">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
