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
    <div class="py-6 max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-4">
            <div class="flex items-center gap-2 flex-wrap justify-center w-full md:w-auto">
                <h1 class="text-2xl font-bold">Programaciones</h1>
                <form method="GET" action="<?php echo e(route('admin.programaciones.index')); ?>" class="flex gap-2 items-center">
                    <select name="mes" id="mes" class="border px-3 py-1.5 rounded w-40 text-sm" onchange="this.form.submit()">
                        <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m); ?>" <?php echo e(request('mes', now()->month) == $m ? 'selected' : ''); ?>>
                                <?php echo e(\Carbon\Carbon::create()->month($m)->locale('es')->monthName); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="anio" id="anio" class="border px-3 py-1.5 rounded w-32 text-sm" onchange="this.form.submit()">
                        <?php for($year = now()->year; $year >= 2020; $year--): ?>
                            <option value="<?php echo e($year); ?>" <?php echo e(request('anio', now()->year) == $year ? 'selected' : ''); ?>><?php echo e($year); ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>
            <a href="<?php echo e(route('admin.programaciones.create')); ?>" class="bg-[#00AF40] text-white px-4 py-2 rounded hover:bg-green-700 text-sm mt-4 md:mt-0">
                ➕ Nueva Programación
            </a>
        </div>

        
        <div class="bg-white p-4 rounded shadow-md mb-4">
            <form method="GET" action="<?php echo e(route('admin.programaciones.index')); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php if(auth()->user()->esAdministrador() && is_null(auth()->user()->coordinacion_id)): ?>
                    <div class="md:col-span-1">
                        <label for="coordinacion_id" class="block text-sm text-gray-700 mb-1">Coordinación</label>
                        <select name="coordinacion_id" id="coordinacion_id" class="w-full border px-4 py-2 rounded min-w-[19rem] text-sm" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            <?php $__currentLoopData = $coordinaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coordinacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($coordinacion->id); ?>" <?php echo e(request('coordinacion_id') == $coordinacion->id ? 'selected' : ''); ?>>
                                    <?php echo e($coordinacion->nombre); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="md:col-span-1">
                    <label for="grupo_id" class="block text-sm text-gray-700 mb-1">Grupo</label>
                    <select name="grupo_id" id="grupo_id" class="w-full border px-4 py-2 rounded min-w-[19rem] text-sm" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <?php $__currentLoopData = $grupos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($grupo->id); ?>" <?php echo e(request('grupo_id') == $grupo->id ? 'selected' : ''); ?>>
                                <?php echo e($grupo->nombre); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="md:col-span-1 flex items-end">
                    <div class="relative w-full">
                        <input type="text" name="buscar" id="buscar" placeholder="Buscar" value="<?php echo e(request('buscar')); ?>"
                            class="w-full border px-4 py-2 pl-4 pr-10 rounded-full text-sm" />
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        
        <div class="bg-white rounded shadow overflow-x-auto">
            <?php $__empty_1 = true; $__currentLoopData = $programacionesAgrupadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupoNombre => $bloques): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="px-4 py-2 bg-green-50 font-semibold border-b border-green-200">Grupo: <?php echo e($grupoNombre); ?></div>
                <?php $__currentLoopData = $bloques; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bloqueCodigo => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-4 py-2 text-sm bg-gray-50 border-b text-gray-700">Bloque: <?php echo e($bloqueCodigo ?: '—'); ?></div>
                    <table class="w-full table-auto text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Curso</th>
                                <th class="px-4 py-2 text-left">Tipo</th>
                                <th class="px-4 py-2 text-left">Duración</th>
                                <th class="px-4 py-2 text-left">Aula</th>
                                <th class="px-4 py-2 text-left">Instructor</th>
                                <th class="px-4 py-2 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $programacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-t">
                                    <td class="px-4 py-2"><?php echo e($programacion->curso->nombre); ?></td>
                                    <td class="px-4 py-2"><?php echo e(ucfirst($programacion->curso->tipo ?? '-')); ?></td>
                                    <td class="px-4 py-2"><?php echo e($programacion->curso->duracion_horas); ?>h</td>
                                    <td class="px-4 py-2"><?php echo e($programacion->aula->nombre ?? '-'); ?></td>
                                    <td class="px-4 py-2">
                                        <?php if($programacion->instructor): ?>
                                            <a href="mailto:<?php echo e($programacion->instructor->correo); ?>" class="text-[#00AF40] hover:underline">
                                                <?php echo e($programacion->instructor->nombre); ?>

                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-500">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2 flex gap-2">
                                    <a href="<?php echo e(route('admin.programaciones.edit', $programacion)); ?>" class="text-[#00AF40] hover:underline text-sm">Editar</a>


                                        <form action="<?php echo e(route('admin.programaciones.destroy', $programacion)); ?>" method="POST" onsubmit="return confirm('¿Eliminar esta programación?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-red-600 hover:underline text-sm">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="px-4 py-6 text-center text-gray-500">No hay programaciones disponibles.</div>
            <?php endif; ?>
        </div>

        <div class="mt-4">
            <?php echo e($programaciones->appends(request()->query())->links()); ?>

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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/programaciones/index.blade.php ENDPATH**/ ?>