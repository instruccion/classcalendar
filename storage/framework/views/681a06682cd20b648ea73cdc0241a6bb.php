<div class="py-6 max-w-6xl mx-auto"
     x-data="ordenarBloque({
         cursosIniciales: <?php echo e(Js::from($cursosParaVista)); ?>,
         feriados: <?php echo e(Js::from($feriados)); ?>,
         grupoId: <?php echo e($grupo->id); ?>,
         bloqueCodigoOriginal: '<?php echo e($bloque_codigo ?? '_sin_codigo_'); ?>',
         rutaUpdateBloque: '<?php echo e(route('admin.programaciones.bloque.update', ['grupo' => $grupo->id, 'bloque_codigo' => $bloque_codigo ?? '_sin_codigo_'])); ?>',
         cursosDisponibles: <?php echo e(Js::from($cursosDisponibles ?? [])); ?>

     })" x-init="init()">

    <div class="bg-white p-6 rounded shadow-md">
        <?php if(session('success')): ?>
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                 class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded shadow">
                ✅ <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <form x-ref="formGuardarBloque" method="POST" :action="rutaUpdateBloque">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/programaciones/bloque/partials/formulario.blade.php ENDPATH**/ ?>