<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar Cursos por Bloque
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto"
         x-data="bloqueForm({
            grupos: {{ Js::from($grupos) }},
            rutaCursos: '{{ route('admin.programaciones.bloque.cursos', ['grupo_id' => ':grupoId']) }}',
            rutaOrdenar: '{{ route('admin.programaciones.bloque.ordenar') }}'
         })">

        <div class="bg-white p-6 rounded shadow-md">
            <div class="flex justify-between items-center mb-6 pb-3 border-b">
                <h1 class="text-2xl font-bold">Programar por Bloque: Selección de Cursos</h1>
                <a href="{{ route('admin.programaciones.create') }}" class="text-blue-600 hover:underline text-sm">
                    ← Volver a Programación Individual
                </a>
            </div>

            <form method="POST" action="{{ route('admin.programaciones.bloque.store') }}" @submit.prevent="submitForm">
                @csrf

                <input type="hidden" name="grupo_id" value="{{ $grupo->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium">Código de Bloque</label>
                        <input type="text" name="bloque_codigo" class="w-full rounded border px-3 py-2" x-model="bloqueCodigo">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Fecha Inicio del Bloque</label>
                        <input type="date" class="w-full rounded border px-3 py-2" x-model="fechaBloque">
                    </div>
                </div>

                <div class="mt-4 text-right">
                    <button type="button" @click="calcularBloque"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                        Calcular Fechas y Horas
                    </button>
                </div>

                <ul class="space-y-4 mt-6" x-ref="sortableList">
                    <template x-for="(curso, index) in cursos" :key="curso.id">
                        <li class="border rounded p-4 bg-gray-50 shadow-sm">
                            <div class="flex justify-between items-center mb-2">
                                <strong class="text-blue-800" x-text="curso.nombre"></strong>
                                <span class="text-xs text-gray-500">Orden: <span x-text="index + 1"></span></span>
                            </div>

                            <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium">Fecha Inicio</label>
                                    <input type="date" class="w-full rounded border px-3 py-2" :name="`cursos[${index}][fecha_inicio]`" x-model="curso.fecha_inicio" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium">Hora Inicio</label>
                                    <input type="time" class="w-full rounded border px-3 py-2" :name="`cursos[${index}][hora_inicio]`" x-model="curso.hora_inicio" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium">Hora Fin</label>
                                    <input type="time" class="w-full rounded border px-3 py-2" :name="`cursos[${index}][hora_fin]`" x-model="curso.hora_fin" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium">Fecha Fin</label>
                                    <input type="date" class="w-full rounded border px-3 py-2" :name="`cursos[${index}][fecha_fin]`" x-model="curso.fecha_fin" required>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>

                <div class="mt-6 text-center">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow">
                        Guardar Programación en Bloque
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function ordenarBloque(config) {
        return {
            cursos: config.cursosIniciales || [],
            feriados: config.feriados || [],
            fechaBloque: '',
            bloqueCodigo: '',

            calcularBloque() {
                if (!this.fechaBloque) return alert('Seleccione una fecha de inicio');

                const HORARIOS = [
                    { inicio: '08:30', fin: '12:00' },
                    { inicio: '13:00', fin: '17:00' }
                ];

                const MINUTOS_HORA_ACADEMICA = 50;
                const feriados = new Set(this.feriados);
                let cursor = new Date(`${this.fechaBloque}T08:30`);

                this.cursos.forEach((curso) => {
                    const duracionMin = curso.duracion_horas * MINUTOS_HORA_ACADEMICA;
                    let minutosPendientes = duracionMin;

                    while (minutosPendientes > 0) {
                        while ([0, 6].includes(cursor.getDay()) || feriados.has(cursor.toISOString().slice(0, 10))) {
                            cursor.setDate(cursor.getDate() + 1);
                            cursor.setHours(8, 30);
                        }

                        let asignado = false;
                        for (let tramo of HORARIOS) {
                            const inicio = new Date(`${cursor.toDateString()}T${tramo.inicio}`);
                            const fin = new Date(`${cursor.toDateString()}T${tramo.fin}`);

                            if (cursor <= fin) {
                                const desde = new Date(cursor);
                                const disponible = (fin - desde) / 60000;
                                const aUsar = Math.min(disponible, minutosPendientes);

                                const horaInicio = new Date(cursor);
                                const horaFin = new Date(cursor.getTime() + aUsar * 60000);

                                curso.fecha_inicio = horaInicio.toISOString().slice(0, 10);
                                curso.hora_inicio = horaInicio.toTimeString().slice(0, 5);
                                curso.fecha_fin = horaFin.toISOString().slice(0, 10);
                                curso.hora_fin = horaFin.toTimeString().slice(0, 5);

                                minutosPendientes -= aUsar;
                                cursor = new Date(horaFin);

                                if (curso.hora_fin >= '15:00') cursor.setDate(cursor.getDate() + 1);
                                asignado = true;
                                break;
                            }
                        }

                        if (!asignado) {
                            cursor.setDate(cursor.getDate() + 1);
                            cursor.setHours(8, 30);
                        }
                    }
                });
            },

            submitForm() {
                this.$root.querySelector('form').submit();
            }
        }
    }
    </script>
</x-app-layout>
