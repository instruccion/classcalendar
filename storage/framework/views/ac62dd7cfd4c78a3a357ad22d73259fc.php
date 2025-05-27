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
        <h2 class="text-xl font-semibold text-gray-800">🧑‍🏫 Gestión de Instructores</h2>
     <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Listado de Instructores</h1>
            <button onclick="abrirModalInstructor()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Nuevo Instructor
            </button>
        </div>

        <div class="overflow-x-auto bg-white p-4 rounded shadow">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Correo</th>
                        <th class="px-4 py-2 text-left">Teléfono</th>
                        <th class="px-4 py-2 text-left">Coordinaciones</th>
                        <th class="px-4 py-2 text-left">Cursos</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $instructores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instructor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $tieneVencido = $instructor->documentosVencidos()->count() > 0; ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-2 font-semibold <?php echo e($tieneVencido ? 'text-red-600' : 'text-gray-800'); ?>">
                                <?php echo e($instructor->nombre); ?>

                            </td>
                            <td class="px-4 py-2"><?php echo e($instructor->correo ?: '—'); ?></td>
                            <td class="px-4 py-2"><?php echo e($instructor->telefono ?: '—'); ?></td>
                            <td class="px-4 py-2"><?php echo e($instructor->coordinaciones->pluck('nombre')->join(', ') ?: '—'); ?></td>
                            <td class="px-4 py-2"><?php echo e($instructor->cursos->pluck('nombre')->join(', ') ?: '—'); ?></td>
                            <td class="px-4 py-2 flex flex-wrap gap-3">
                                <a href="<?php echo e(route('admin.instructores.documentos', $instructor)); ?>" class="text-yellow-600 hover:underline">📄 Documentos</a>
                                <button onclick='editarInstructor(<?php echo json_encode($instructor, 15, 512) ?>)' class="text-blue-600 hover:underline">Editar</button>
                                <form action="<?php echo e(route('admin.instructores.destroy', $instructor)); ?>" method="POST" onsubmit="return confirm('¿Eliminar este instructor?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-center text-gray-500 py-4">No hay instructores registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php echo $__env->make('admin.instructores.partials.modal-form', [
        'coordinaciones' => \App\Models\Coordinacion::all(),
        'cursos' => \App\Models\Curso::all()
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(session('toast')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Livewire?.emit?.('toast', {
                    type: '<?php echo e(session('toast.type')); ?>',
                    message: '<?php echo e(session('toast.message')); ?>'
                });
            });
        </script>
    <?php endif; ?>

    <script>
        function abrirModalInstructor() {
            const form = document.getElementById('formInstructor');
            form.reset();
            form.action = "<?php echo e(route('admin.instructores.store')); ?>";
            document.getElementById('modalTitulo').textContent = 'Registrar Instructor';
            document.getElementById('modalInstructor').showModal();
            document.getElementById('_method')?.remove();
            document.querySelectorAll('#coordinacion_ids option, #curso_ids option').forEach(opt => opt.selected = false);
        }

        function editarInstructor(data) {
            const form = document.getElementById('formInstructor');
            const url = `<?php echo e(url('admin/instructores')); ?>/${data.id}`;
            form.action = url;
            document.getElementById('modalTitulo').textContent = 'Editar Instructor';
            form.nombre.value = data.nombre;
            form.especialidad.value = data.especialidad || '';
            form.correo.value = data.correo || '';
            form.telefono.value = data.telefono || '';

            if (!document.getElementById('_method')) {
                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PUT';
                method.id = '_method';
                form.appendChild(method);
            }

            const coordSel = document.getElementById('coordinacion_ids');
            [...coordSel.options].forEach(option => {
                option.selected = data.coordinaciones?.some(co => co.id == option.value);
            });

            const cursoSel = document.getElementById('curso_ids');
            [...cursoSel.options].forEach(option => {
                option.selected = data.cursos?.some(cu => cu.id == option.value);
            });

            document.getElementById('modalInstructor').showModal();
        }
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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/instructores/index.blade.php ENDPATH**/ ?>