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
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 text-center mb-6">📅 Calendario de Cursos</h1>

        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 justify-center mb-6 max-w-4xl mx-auto">
            <?php if($usuario->rol === 'administrador'): ?>
                <div>
                    <label for="coordinacion" class="block text-sm font-medium text-gray-700">Coordinación:</label>
                    <select id="coordinacion" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                        <option value="">Todas</option>
                        <?php $__currentLoopData = $coordinaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($coor->id); ?>"><?php echo e($coor->nombre); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            <?php endif; ?>

            <div>
                <label for="grupo" class="block text-sm font-medium text-gray-700">Grupo:</label>
                <select name="grupo" id="grupo" class="mt-1 block w-full border rounded px-3 py-2 text-sm">
                    <option value="">Todos los grupos</option>
                    <?php $__currentLoopData = $grupos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($grupo->id); ?>"><?php echo e($grupo->nombre); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">
                    Filtrar
                </button>
            </div>
        </form>

    </div>

    <div id="calendar-container" class="bg-white rounded-lg shadow p-4 mt-6"
         data-coordinacion-id="<?php echo e($coordinacionId); ?>">
        <div id="calendar" class="w-full"></div>
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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/calendario/index.blade.php ENDPATH**/ ?>