
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
            📅 Agenda de <?php echo e($selectedInstructor ? $selectedInstructor->nombre : 'Instructores'); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8"> 
        
        <div class="bg-white shadow sm:rounded-lg p-4"> 
            <form method="GET" action="<?php echo e(route('admin.instructores.agenda')); ?>" class="max-w-md">
                <label for="instructor_id" class="block font-medium text-sm text-gray-700 mb-1">Selecciona un instructor:</label>
                <select name="instructor_id" id="instructor_id" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">-- Elegir instructor --</option>
                    <?php $__currentLoopData = $instructores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($inst->id); ?>" <?php if(request('instructor_id') == $inst->id): echo 'selected'; endif; ?>> 
                            <?php echo e($inst->nombre); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                 
                 <?php if(request('instructor_id')): ?>
                    <a href="<?php echo e(route('admin.instructores.agenda')); ?>" class="text-sm text-gray-600 hover:text-gray-900 mt-1 inline-block">Limpiar selección</a>
                 <?php endif; ?>
            </form>
        </div>

        
        <?php if($instructor_id && $programaciones->count()): ?>
            <div class="bg-white shadow sm:rounded-lg p-4 overflow-x-auto">
                <h3 class="font-semibold text-lg mb-3 text-gray-800">Cursos asignados a <?php echo e($selectedInstructor->nombre); ?></h3>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider"> 
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left">Curso</th>
                            <th scope="col" class="px-4 py-2 text-left">Grupo</th>
                            <th scope="col" class="px-4 py-2 text-left">Inicio</th>
                            <th scope="col" class="px-4 py-2 text-left">Fin</th>
                            <th scope="col" class="px-4 py-2 text-left">Horario</th>
                            <th scope="col" class="px-4 py-2 text-left">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200"> 
                        <?php $__currentLoopData = $programaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50"> 
                                <td class="px-4 py-2 whitespace-nowrap"><?php echo e($p->curso->nombre ?? '—'); ?></td>
                                <td class="px-4 py-2 whitespace-nowrap"><?php echo e($p->grupo->nombre ?? '—'); ?></td>
                                <td class="px-4 py-2 whitespace-nowrap"><?php echo e($p->fecha_inicio?->format('d/m/Y') ?? 'N/A'); ?></td>
                                <td class="px-4 py-2 whitespace-nowrap"><?php echo e($p->fecha_fin?->format('d/m/Y') ?? 'N/A'); ?></td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <?php if($p->hora_inicio && $p->hora_fin): ?>
                                        <?php echo e($p->hora_inicio instanceof \Carbon\Carbon ? $p->hora_inicio->format('H:i') : $p->hora_inicio); ?> -
                                        <?php echo e($p->hora_fin instanceof \Carbon\Carbon ? $p->hora_fin->format('H:i') : $p->hora_fin); ?>

                                    <?php else: ?>
                                        — —
                                    <?php endif; ?>
                                </td>


                                <td class="px-4 py-2 whitespace-nowrap">
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $p->estado_confirmacion === 'confirmado',
                                        'bg-red-100 text-red-800' => $p->estado_confirmacion === 'rechazado',
                                        'bg-yellow-100 text-yellow-800' => $p->estado_confirmacion !== 'confirmado' && $p->estado_confirmacion !== 'rechazado',
                                    ]); ?>">
                                        <?php echo e(ucfirst($p->estado_confirmacion ?? 'pendiente')); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php elseif($instructor_id): ?>
             <div class="bg-white shadow sm:rounded-lg p-4">
                 <p class="text-center text-gray-500">No hay cursos asignados para <?php echo e($selectedInstructor->nombre); ?>.</p>
             </div>
        <?php else: ?>
             <div class="bg-white shadow sm:rounded-lg p-4">
                 <p class="text-center text-gray-500">Selecciona un instructor para ver su agenda.</p>
             </div>
        <?php endif; ?>

        
        <?php if($instructor_id): ?>
            <div class="bg-white shadow sm:rounded-lg p-6">
                
                
                
                
                <div id='instructor-agenda-calendar'></div>
            </div>
        <?php endif; ?>
    </div>

    
    <dialog id="modalDetalle" class="rounded-lg shadow-xl p-0 w-full max-w-lg overflow-hidden">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center mb-4">
                 <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Detalles del Curso</h3>
                 <button onclick="document.getElementById('modalDetalle').close()" class="text-gray-400 hover:text-gray-600">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>
            </div>

            <div id="modalContent" class="text-sm text-gray-700 space-y-2">
                
                <p>Cargando...</p>
            </div>
            <div class="mt-6 text-right">
                <button onclick="document.getElementById('modalDetalle').close()" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cerrar
                </button>
            </div>
        </div>

    </dialog>

    <?php $__env->startPush('scripts'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/calendar-instructor.js']); ?>

<?php $__env->stopPush(); ?>


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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/instructores/agenda.blade.php ENDPATH**/ ?>