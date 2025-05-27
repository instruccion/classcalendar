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
            Auditorías
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-4 max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800">Registros de Auditoría</h1>

        <!-- Tabla de Auditorías -->
        <div class="overflow-x-auto bg-white shadow rounded-lg mt-6">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2">Acción</th>
                        <th class="px-4 py-2">Descripción</th>
                        <th class="px-4 py-2">Usuario</th>
                        <th class="px-4 py-2">IP</th>
                        <th class="px-4 py-2">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $auditorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $auditoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?php echo e($auditoria->accion); ?></td>
                            <td class="px-4 py-2"><?php echo e($auditoria->descripcion); ?></td>
                            <td class="px-4 py-2"><?php echo e($auditoria->user->name); ?></td>
                            <td class="px-4 py-2"><?php echo e($auditoria->ip); ?></td>
                            <td class="px-4 py-2"><?php echo e($auditoria->created_at->format('d-m-Y H:i')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">No se encontraron auditorías.</td>
                        </tr>
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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/auditorias/index.blade.php ENDPATH**/ ?>