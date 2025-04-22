<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Programar Cursos por Bloque – {{ $grupo->nombre }} ({{ $grupo->coordinacion?->nombre ?? 'Sin Coordinación' }})
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto"
         x-data="bloqueOrdenado({
            cursosIniciales: {{ Js::from($cursosSeleccionados) }},
            duraciones: {{ Js::from($cursosSeleccionados->pluck('duracion_horas', 'id')) }},
            feriados: {{ Js::from($feriados ?? []) }}
         })">

        <div class="bg-white p-6 rounded shadow-md">
            <div class="mb-6">
                <h1 class="text-2xl font-bold mb-2">Paso 2: Confirmar y Calcular Programación por Bloque</h1>
                <p class="text-sm text-gray-600">Los cursos se programarán secuencialmente respetando horarios laborales. Puedes ajustar manualmente si lo deseas.</p>
            </div>

            <form method="POST" action="{{ route('admin.programaciones.bloque.store') }}" @submit.prevent="submitForm">
                @csrf
                <input type="hidden" name="grupo_id" value="{{ $grupo->id }}">

                <div class="grid md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium">Código de Bloque</label>
                        <input type="text" name="bloque_codigo" x-model="bloqueCodigo" required class="w-full rounded border px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Fecha de Inicio del Bloque</label>
                        <input type="date" x-model="fechaInicioBloque" @change="recalcularFechas()" required class="w-full rounded border px-3 py-2">
                    </div>
                </div>

                <template x-for="(curso, index) in cursos" :key="curso.id">
                    <div class="mb-6 border rounded p-4 bg-gray-50 shadow-sm">
                        <input type="hidden" :name="`cursos[${index}][id]`" :value="curso.id">

                        <h3 class="font-semibold text-blue-800 text-lg mb-3" x-text="curso.nombre"></h3>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Fecha Inicio</label>
                                <input type="date" class="w-full rounded border px-3 py-2"
                                       :name="`cursos[${index}][fecha_inicio]`" x-model="curso.fecha_inicio" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Hora Inicio</label>
                                <input type="time" class="w-full rounded border px-3 py-2"
                                       :name="`cursos[${index}][hora_inicio]`" x-model="curso.hora_inicio" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Fecha Fin</label>
                                <input type="date" class="w-full rounded border px-3 py-2"
                                       :name="`cursos[${index}][fecha_fin]`" x-model="curso.fecha_fin" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Hora Fin</label>
                                <input type="time" class="w-full rounded border px-3 py-2"
                                       :name="`cursos[${index}][hora_fin]`" x-model="curso.hora_fin" required>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="mt-6 text-center">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow">
                        Confirmar Programación por Bloque
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
function ordenarBloque(config) {
    return {
        cursos: config.cursosIniciales || [],
        feriados: config.feriados || [], // Array de fechas 'YYYY-MM-DD'
        fechaBloque: '',
        bloqueCodigo: '',

        calcularBloque() {
            if (!this.fechaBloque) return alert('Seleccione una fecha de inicio');

            // Horario y constantes
            const HORAS_LABORAL = [
                { inicio: '08:30', fin: '12:00' },
                { inicio: '13:00', fin: '17:00' }
            ];
            const MINUTOS_POR_HORA_ACADEMICA = 50;

            const feriados = new Set(this.feriados);

            let cursor = new Date(`${this.fechaBloque}T08:30`);

            this.cursos.forEach((curso, index) => {
                const duracion = curso.duracion_horas * MINUTOS_POR_HORA_ACADEMICA;
                let minutosRestantes = duracion;

                while (minutosRestantes > 0) {
                    // Saltar sábados, domingos y feriados
                    while ([0, 6].includes(cursor.getDay()) || feriados.has(cursor.toISOString().slice(0, 10))) {
                        cursor.setDate(cursor.getDate() + 1);
                        cursor.setHours(8, 30);
                    }

                    // Buscar tramo disponible
                    let asignado = false;
                    for (let tramo of HORAS_LABORAL) {
                        const inicio = new Date(cursor);
                        const fin = new Date(cursor.toDateString() + 'T' + tramo.fin);
                        inicio.setHours(...tramo.inicio.split(':').map(Number));

                        if (cursor <= fin) {
                            const tramoMin = (new Date(`${cursor.toDateString()}T${tramo.fin}`) - new Date(`${cursor.toDateString()}T${tramo.inicio}`)) / 60000;
                            const desdeMin = Math.max(0, (cursor - inicio) / 60000);
                            const disponible = tramoMin - desdeMin;
                            const usados = Math.min(disponible, minutosRestantes);

                            const horaInicio = new Date(cursor);
                            const horaFin = new Date(cursor.getTime() + usados * 60000);

                            curso.fecha_inicio = horaInicio.toISOString().slice(0, 10);
                            curso.hora_inicio = horaInicio.toTimeString().slice(0, 5);
                            curso.fecha_fin = horaFin.toISOString().slice(0, 10);
                            curso.hora_fin = horaFin.toTimeString().slice(0, 5);

                            minutosRestantes -= usados;
                            cursor = new Date(horaFin);

                            asignado = true;
                            break;
                        }
                    }

                    if (!asignado) {
                        // Ir al siguiente día laboral
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
