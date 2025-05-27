<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Editar Bloque – <?php echo e($grupo->nombre); ?> (<?php echo e($grupo->coordinacion?->nombre ?? 'Sin Coordinación'); ?>)
        </h2>
     <?php $__env->endSlot(); ?>

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

                    const formatDate = date => date.toISOString().split('T')[0];
                    const formatTime = date => date.toTimeString().slice(0, 5);
                    const esFeriado = fecha => feriados.has(formatDate(fecha));
                    const esFinDeSemana = fecha => [0, 6].includes(fecha.getDay());

                    const pasarAlDiaHabil = timestamp => {
                        let f = new Date(timestamp);
                        do {
                            f.setDate(f.getDate() + 1);
                        } while (esFinDeSemana(f) || esFeriado(f));
                        f.setHours(8, 30, 0, 0);
                        return f.getTime();
                    };

                    const ajustarCursor = timestamp => {
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

    <?php echo $__env->make('admin.programaciones.bloque.partials.formulario', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/programaciones/bloque/edit.blade.php ENDPATH**/ ?>