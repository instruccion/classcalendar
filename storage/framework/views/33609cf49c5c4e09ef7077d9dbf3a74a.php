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
            <?php echo e(__('Editar Perfil')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-8 max-w-3xl mx-auto space-y-6">
        <form method="POST" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>

            
            <div class="flex items-center gap-6 mb-6">
                <div class="flex-shrink-0">
                    <img id="preview-img"
                         src="<?php echo e(asset('assets/images/users/' . ($user->foto_perfil ?? 'avatar-default.png'))); ?>"
                         class="w-28 h-28 rounded-full object-cover ring ring-indigo-500 shadow"
                         alt="Foto de perfil">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cambiar foto</label>
                    <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*"
                        class="text-sm text-gray-600
                               file:mr-4 file:py-2 file:px-4
                               file:rounded-full file:border-0
                               file:text-sm file:font-semibold
                               file:bg-indigo-50 file:text-indigo-700
                               hover:file:bg-indigo-100">
                </div>
            </div>

            
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input id="name" type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" required
                    class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                <input id="email" type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required
                    class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            
            <div class="mb-4">
                <label for="coordinacion_id" class="block text-sm font-medium text-gray-700">Coordinación</label>
                <?php if(Auth::user()->rol === 'administrador'): ?>
                    <select id="coordinacion_id" name="coordinacion_id"
                        class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Seleccione una coordinación</option>
                        <?php $__currentLoopData = $coordinaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coordinacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($coordinacion->id); ?>"
                                <?php echo e(old('coordinacion_id', $user->coordinacion_id) == $coordinacion->id ? 'selected' : ''); ?>>
                                <?php echo e($coordinacion->nombre); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php else: ?>
                    <input type="text" disabled readonly
                        class="w-full mt-1 bg-gray-100 text-gray-700 rounded-md border border-gray-300 shadow-sm"
                        value="<?php echo e($user->coordinacion->nombre ?? '—'); ?>">
                <?php endif; ?>
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition">
                    Actualizar Perfil
                </button>
            </div>
        </form>
    </div>

    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('foto_perfil');
            const preview = document.getElementById('preview-img');

            input?.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/profile/edit.blade.php ENDPATH**/ ?>