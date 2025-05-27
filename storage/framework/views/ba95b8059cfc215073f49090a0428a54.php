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
        <h2 class="text-xl font-semibold text-gray-800">Coordinaciones</h2>
     <?php $__env->endSlot(); ?>

    <div class="py-4 max-w-6xl mx-auto">

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Coordinaciones</h1>
            <button onclick="window.location.href='<?php echo e(route('admin.coordinaciones.create')); ?>'"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Nueva Coordinación
            </button>
        </div>

        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Color</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $coordinaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?php echo e($coor->nombre); ?></td>
                            <td class="px-4 py-2">
                                <span class="inline-block w-6 h-6 rounded-full" style="background-color: <?php echo e($coor->color); ?>;"></span>
                            </td>
                            <td class="px-4 py-2 flex gap-3">
                                <!-- Editar -->
                                <a href="<?php echo e(route('admin.coordinaciones.edit', $coor)); ?>" class="text-blue-600 hover:underline">Editar</a>

                                <!-- Eliminar -->
                                <form action="<?php echo e(route('admin.coordinaciones.destroy', $coor)); ?>" method="POST" onsubmit="return confirm('¿Eliminar esta coordinación?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="text-center py-4 text-gray-500">No hay coordinaciones registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/coordinaciones/index.blade.php ENDPATH**/ ?>