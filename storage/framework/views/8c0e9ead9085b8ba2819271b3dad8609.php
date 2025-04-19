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
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">Gestión de Cursos</h2>
     <?php $__env->endSlot(); ?>

    <div class="py-4 max-w-7xl mx-auto">
        <div class="mb-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Cursos Registrados</h1>
            <button onclick="document.getElementById('modalNuevoCurso').showModal()"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Registrar Nuevo Curso
            </button>
        </div>

        <!-- Filtros -->
        <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php if($usuario->rol === 'administrador'): ?>
                <div>
                    <label for="coordinacion" class="block text-sm font-medium text-gray-700">Coordinación</label>
                    <select id="coordinacion" name="coordinacion_id" class="mt-1 block w-full border rounded px-3 py-2">
                        <option value="">Todas</option>
                        <?php $__currentLoopData = $coordinaciones ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($coor->id); ?>" <?php echo e(request('coordinacion_id') == $coor->id ? 'selected' : ''); ?>>
                                <?php echo e($coor->nombre); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            <?php endif; ?>

            <div>
                <label for="grupo" class="block text-sm font-medium text-gray-700">Grupo</label>
                <select name="grupo_id" id="grupo" class="mt-1 block w-full border rounded px-3 py-2">
                    <option value="">Todos los grupos</option>
                    <?php $__currentLoopData = $grupos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($grupo->id); ?>" <?php echo e(request('grupo_id') == $grupo->id ? 'selected' : ''); ?>>
                            <?php echo e($grupo->nombre); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Filtrar
                </button>
            </div>
        </form>

        <!-- Tabla de cursos -->
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2">Nombre</th>
                        <th class="px-4 py-2">Tipo</th>
                        <th class="px-4 py-2">Duración</th>
                        <th class="px-4 py-2">Grupos</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $cursos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $curso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?php echo e($curso->nombre); ?></td>
                            <td class="px-4 py-2"><?php echo e($curso->tipo); ?></td>
                            <td class="px-4 py-2"><?php echo e($curso->duracion_horas); ?> h</td>
                            <td class="px-4 py-2">
                                <?php echo e($curso->grupos->pluck('nombre')->join(', ')); ?>

                            </td>
                            <td class="px-4 py-2 flex gap-2">
                            <button type="button" onclick="abrirModalEditarCurso(<?php echo e($curso->id); ?>)"
                                class="text-blue-600 hover:underline">Editar</button>

                                <form action="<?php echo e(route('admin.cursos.destroy', $curso)); ?>" method="POST" onsubmit="return confirm('¿Eliminar este curso?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">No hay cursos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal de nuevo curso -->
        <?php echo $__env->make('admin.cursos.partials.modal-nuevo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    
    <?php echo $__env->make('admin.cursos.partials.modal-editar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        const URL_GRUPOS_POR_COORDINACION = "<?php echo e(route('admin.grupos.por.coordinacion', ':id')); ?>";
    </script>


    <script>
    const BASE_URL_EDITAR_CURSO = "<?php echo e(url('admin/cursos')); ?>";

    function abrirModalEditarCurso(cursoId) {
        fetch(`${BASE_URL_EDITAR_CURSO}/${cursoId}/edit`)
            .then(response => {
                if (!response.ok) throw new Error('Error de red');
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    Livewire?.emit?.('toast', { type: 'error', message: data.error });
                    return;
                }

                document.getElementById('curso_edit_id').value = data.id;
                document.getElementById('curso_edit_nombre').value = data.nombre;
                document.getElementById('curso_edit_tipo').value = data.tipo;
                document.getElementById('curso_edit_duracion').value = data.duracion_horas;
                document.getElementById('curso_edit_descripcion').value = data.descripcion ?? '';

                document.querySelectorAll('#curso_edit_grupos input[type=checkbox]').forEach(cb => cb.checked = false);
                if (Array.isArray(data.grupo_ids)) {
                    data.grupo_ids.forEach(id => {
                        const checkbox = document.querySelector(`#curso_edit_grupos input[value="${id}"]`);
                        if (checkbox) checkbox.checked = true;
                    });
                }

                document.getElementById('formEditarCurso').action = `${BASE_URL_EDITAR_CURSO}/${cursoId}`;
                document.getElementById('modalEditarCurso').showModal();
            })
            .catch(() => {
                Livewire?.emit?.('toast', { type: 'error', message: 'Error al cargar datos del curso.' });
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const coordinacionSelect = document.getElementById('coordinacion');
        const grupoSelect = document.getElementById('grupo');

        if (coordinacionSelect && grupoSelect) {
            coordinacionSelect.addEventListener('change', () => {
                const coordinacionId = coordinacionSelect.value;

                grupoSelect.innerHTML = '<option selected>Cargando grupos...</option>';

                if (!coordinacionId) {
                    grupoSelect.innerHTML = '<option value="">Todos los grupos</option>';
                    return;
                }

                // Aquí usamos la constante definida previamente
                const url = URL_GRUPOS_POR_COORDINACION.replace(':id', coordinacionId);

                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error('Error al cargar grupos');
                        return response.json();
                    })
                    .then(grupos => {
                        grupoSelect.innerHTML = '<option value="">Todos los grupos</option>';
                        grupos.forEach(grupo => {
                            const option = document.createElement('option');
                            option.value = grupo.id;
                            option.textContent = grupo.nombre;
                            grupoSelect.appendChild(option);
                        });
                    })
                    .catch((error) => {
                        grupoSelect.innerHTML = `<option>${error.message}</option>`;
                        Livewire?.emit?.('toast', { type: 'error', message: error.message });
                    });
            });
        }
    });


</script>





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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/cursos/index.blade.php ENDPATH**/ ?>