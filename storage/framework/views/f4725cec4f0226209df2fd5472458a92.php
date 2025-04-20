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
            Editar Programación
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-4xl mx-auto">
        <div class="bg-white p-6 rounded shadow-md">
            <form method="POST" action="<?php echo e(route('admin.programaciones.update', $programacion)); ?>" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                
                <div>
                    <label for="grupo_id" class="block font-semibold mb-1">Grupo <span class="text-red-500">*</span></label>
                    <select name="grupo_id" id="grupo_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un grupo...</option>
                        <?php $__currentLoopData = $grupos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($grupo->id); ?>" <?php echo e($programacion->grupo_id == $grupo->id ? 'selected' : ''); ?>>
                                <?php echo e($grupo->nombre); ?> (<?php echo e($grupo->coordinacion?->nombre ?? 'Sin coordinación'); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div>
                    <label for="curso_id" class="block font-semibold mb-1">Curso <span class="text-red-500">*</span></label>
                    <select name="curso_id" id="curso_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un curso...</option>
                        <?php $__currentLoopData = $cursos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $curso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($curso->id); ?>" <?php echo e($programacion->curso_id == $curso->id ? 'selected' : ''); ?>>
                                <?php echo e($curso->nombre); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="md:col-span-2 flex items-center gap-4">
                    <label for="bloque_codigo" class="font-semibold">Bloque</label>
                    <input type="text" name="bloque_codigo" id="bloque_codigo" value="<?php echo e($programacion->bloque_codigo); ?>" class="border px-4 py-2 rounded w-full md:w-1/3">
                </div>

                
                <div>
                    <label for="fecha_inicio" class="block font-semibold mb-1">Fecha Inicio <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo e($programacion->fecha_inicio->format('Y-m-d')); ?>" required class="w-full border px-4 py-2 rounded">
                </div>

                <div>
                    <label for="hora_inicio" class="block font-semibold mb-1">Hora Inicio <span class="text-red-500">*</span></label>
                    <input type="time" name="hora_inicio" id="hora_inicio" value="<?php echo e($programacion->hora_inicio->format('H:i')); ?>" required class="w-full border px-4 py-2 rounded">
                </div>

                <div>
                    <label for="fecha_fin" class="block font-semibold mb-1">Fecha Fin <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo e($programacion->fecha_fin->format('Y-m-d')); ?>" required class="w-full border px-4 py-2 rounded">
                </div>

                <div>
                    <label for="hora_fin" class="block font-semibold mb-1">Hora Fin <span class="text-red-500">*</span></label>
                    <input type="time" name="hora_fin" id="hora_fin" value="<?php echo e($programacion->hora_fin->format('H:i')); ?>" required class="w-full border px-4 py-2 rounded">
                </div>

                
                <div>
                    <label for="aula_id" class="block font-semibold mb-1">Aula <span class="text-red-500">*</span></label>
                    <select name="aula_id" id="aula_id" required class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Seleccione un aula...</option>
                        <?php $__currentLoopData = $aulas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($aula->id); ?>" <?php echo e($programacion->aula_id == $aula->id ? 'selected' : ''); ?>>
                                <?php echo e($aula->nombre); ?><?php echo e($aula->lugar ? ' – ' . $aula->lugar : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div>
                    <label for="instructor_id" class="block font-semibold mb-1">Instructor</label>
                    <select name="instructor_id" id="instructor_id" class="w-full border px-4 py-2 rounded bg-white">
                        <option value="">Sin instructor</option>
                        <?php $__currentLoopData = $instructores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instructor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($instructor->id); ?>" <?php echo e($programacion->instructor_id == $instructor->id ? 'selected' : ''); ?>>
                                <?php echo e($instructor->nombre); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="md:col-span-2 text-center mt-6">
                    <button type="submit" class="bg-[#00AF40] text-white px-6 py-2 rounded hover:bg-green-700">
                        Guardar Cambios
                    </button>
                </div>
            </form>
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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/programaciones/edit.blade.php ENDPATH**/ ?>