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
        <h2 class="text-xl font-semibold text-gray-800">Grupos</h2>
     <?php $__env->endSlot(); ?>

    <div class="py-4 max-w-6xl mx-auto">
        
        <?php if(session('success')): ?>
            <script>
                window.addEventListener('DOMContentLoaded', () => {
                    toast('success', '<?php echo e(session('success')); ?>');
                });
            </script>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <script>
                window.addEventListener('DOMContentLoaded', () => {
                    toast('error', 'Corrige los errores del formulario.');
                });
            </script>
        <?php endif; ?>

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Grupos</h1>
            <button onclick="document.getElementById('modalNuevoGrupo').showModal()"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Nuevo Grupo
            </button>
        </div>

        
        <?php if(auth()->user()->rol === 'administrador' && is_null(auth()->user()->coordinacion_id)): ?>
            <form method="GET" action="<?php echo e(route('admin.grupos.index')); ?>" class="mb-4 max-w-sm">
                <label for="coordinacion_id" class="block font-semibold mb-1">Filtrar por Coordinación:</label>
                <select name="coordinacion_id" id="coordinacion_id" onchange="this.form.submit()" class="w-full border px-4 py-2 rounded">
                    <option value="">Todas las coordinaciones</option>
                    <?php $__currentLoopData = $coordinaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($coor->id); ?>" <?php echo e(request('coordinacion_id') == $coor->id ? 'selected' : ''); ?>>
                            <?php echo e($coor->nombre); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </form>
        <?php endif; ?>


        
        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Nombre</th>
                        <th class="px-4 py-2 text-left">Coordinación</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $grupos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?php echo e($grupo->nombre); ?></td>
                            <td class="px-4 py-2"><?php echo e($grupo->coordinacion->nombre ?? '—'); ?></td>
                            <td class="px-4 py-2 flex gap-3">
                                <button onclick="abrirModalEditar(<?php echo e($grupo->id); ?>, '<?php echo e($grupo->nombre); ?>', '<?php echo e($grupo->coordinacion_id); ?>')"
                                        class="text-blue-600 hover:underline">Editar</button>
                                <form method="POST" action="<?php echo e(route('admin.grupos.destroy', $grupo)); ?>" onsubmit="return confirm('¿Eliminar grupo?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="text-center py-4 text-gray-500">No hay grupos registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <dialog id="modalNuevoGrupo" class="rounded-lg w-full max-w-xl p-0 overflow-hidden shadow-xl backdrop:bg-black/30">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h2 class="text-xl font-bold">Registrar Grupo</h2>
                <button onclick="document.getElementById('modalNuevoGrupo').close()"
                        class="text-gray-600 hover:text-black text-xl">&times;</button>
            </div>
            <form action="<?php echo e(route('admin.grupos.store')); ?>" method="POST" class="grid grid-cols-1 gap-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block font-semibold mb-1">Nombre</label>
                    <input type="text" name="nombre" value="<?php echo e(old('nombre')); ?>"
                        class="w-full border px-4 py-2 rounded <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                    <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <?php if(auth()->user()->rol === 'administrador'): ?>
                    <div>
                        <label class="block font-semibold mb-1">Coordinación</label>
                        <select name="coordinacion_id"
                                class="w-full border px-4 py-2 rounded <?php $__errorArgs = ['coordinacion_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <option value="">Seleccione una</option>
                            <?php $__currentLoopData = $coordinaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($coor->id); ?>" <?php if(old('coordinacion_id') == $coor->id): echo 'selected'; endif; ?>><?php echo e($coor->nombre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['coordinacion_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="coordinacion_id" value="<?php echo e(auth()->user()->coordinacion_id); ?>">
                <?php endif; ?>

                <div class="text-center mt-2">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    
    <dialog id="modalEditarGrupo" class="rounded-lg w-full max-w-xl p-0 overflow-hidden shadow-xl backdrop:bg-black/30">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h2 class="text-xl font-bold">Editar Grupo</h2>
                <button onclick="document.getElementById('modalEditarGrupo').close()" class="text-gray-600 hover:text-black text-xl">&times;</button>
            </div>
            <form id="formEditarGrupo" method="POST" class="grid grid-cols-1 gap-4">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="id" id="grupo_edit_id">
                <div>
                    <label class="block font-semibold mb-1">Nombre</label>
                    <input type="text" name="nombre" id="grupo_edit_nombre" required class="w-full border px-4 py-2 rounded">
                </div>

                <?php if(auth()->user()->rol === 'administrador'): ?>
                    <div>
                        <label class="block font-semibold mb-1">Coordinación</label>
                        <select name="coordinacion_id" id="grupo_edit_coordinacion" required class="w-full border px-4 py-2 rounded">
                            <?php $__currentLoopData = $coordinaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($coor->id); ?>"><?php echo e($coor->nombre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="coordinacion_id" id="grupo_edit_coordinacion" value="<?php echo e(auth()->user()->coordinacion_id); ?>">
                <?php endif; ?>

                <div class="text-center mt-2">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Actualizar</button>
                </div>
            </form>
        </div>
    </dialog>

    
    <script>
        function abrirModalEditar(id, nombre, coordinacion_id) {
            const modal = document.getElementById('modalEditarGrupo');
            document.getElementById('grupo_edit_id').value = id;
            document.getElementById('grupo_edit_nombre').value = nombre;
            document.getElementById('grupo_edit_coordinacion').value = coordinacion_id;
            document.getElementById('formEditarGrupo').action = `<?php echo e(url('admin/grupos')); ?>/${id}`;
            modal.showModal();
        }

        <?php if($errors->any() && old('nombre')): ?>
            document.getElementById('modalNuevoGrupo')?.showModal();
        <?php endif; ?>

        function toast(type, message) {
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            const toast = document.createElement('div');
            toast.className = `fixed top-5 right-5 text-white px-4 py-2 rounded shadow-lg z-50 ${colors[type]}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/grupos/index.blade.php ENDPATH**/ ?>