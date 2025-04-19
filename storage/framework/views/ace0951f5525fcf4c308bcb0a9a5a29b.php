<dialog id="modalNuevoCurso" class="rounded-lg w-full max-w-4xl p-0 overflow-hidden shadow-xl backdrop:bg-black/30">
    <div class="bg-white p-6">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h2 class="text-xl font-bold">Registrar Nuevo Curso</h2>
            <button onclick="document.getElementById('modalNuevoCurso').close()"
                    class="text-gray-600 hover:text-black text-xl">×</button>
        </div>

        
        <form action="<?php echo e(route('admin.cursos.store')); ?>" method="POST" class="grid grid-cols-12 gap-4">
            <?php echo csrf_field(); ?>

            <!-- Grupo(s) -->
            <div class="col-span-12">
                <label class="block font-semibold mb-1">Asignar a Grupo(s)</label>
                
                
                
                <?php if(!isset($grupos) || $grupos->isEmpty()): ?>
                    <p class="text-sm text-gray-500">No hay grupos disponibles o no se cargaron para este modal.</p>
                <?php else: ?>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 max-h-40 overflow-y-auto p-2 border rounded"> 
                        <?php $__currentLoopData = $grupos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="inline-flex items-center gap-2 text-sm"> 
                                <input type="checkbox" name="grupo_ids[]" value="<?php echo e($g->id); ?>"
                                       class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" 
                                       <?php echo e(is_array(old('grupo_ids')) && in_array($g->id, old('grupo_ids')) ? 'checked' : ''); ?>> 
                                <?php echo e($g->nombre); ?>

                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
                 <?php $__errorArgs = ['grupo_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                    <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Nombre del curso -->
            <div class="col-span-12 md:col-span-6">
                <label for="curso_nombre" class="block font-semibold mb-1">Nombre del Curso</label> 
                <input type="text" id="curso_nombre" name="nombre" required maxlength="100"
                       value="<?php echo e(old('nombre')); ?>"
                       class="w-full border px-4 py-2 rounded <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"> 
                <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Tipo -->
            <div class="col-span-12 md:col-span-4">
                <label for="curso_tipo" class="block font-semibold mb-1">Tipo</label> 
                <select id="curso_tipo" name="tipo" class="w-full border px-4 py-2 rounded <?php $__errorArgs = ['tipo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                    <option value="" disabled <?php echo e(old('tipo') ? '' : 'selected'); ?>>Seleccione tipo...</option> 
                    <option value="inicial" <?php echo e(old('tipo') === 'inicial' ? 'selected' : ''); ?>>Inicial</option>
                    <option value="recurrente" <?php echo e(old('tipo') === 'recurrente' ? 'selected' : ''); ?>>Recurrente</option>
                    <option value="puntual" <?php echo e(old('tipo') === 'puntual' ? 'selected' : ''); ?>>Puntual</option>
                </select>
                 <?php $__errorArgs = ['tipo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Duración -->
            <div class="col-span-12 md:col-span-2">
                <label for="curso_duracion" class="block font-semibold mb-1">Duración (h)</label> 
                <input type="number" id="curso_duracion" name="duracion_horas" required min="1"
                       value="<?php echo e(old('duracion_horas')); ?>"
                       class="w-full border px-4 py-2 rounded <?php $__errorArgs = ['duracion_horas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                 <?php $__errorArgs = ['duracion_horas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Descripción -->
            <div class="col-span-12">
                <label for="curso_descripcion" class="block font-semibold mb-1">Descripción (opcional)</label> 
                <textarea id="curso_descripcion" name="descripcion" rows="3"
                          class="w-full border px-4 py-2 rounded <?php $__errorArgs = ['descripcion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('descripcion')); ?></textarea>
                 <?php $__errorArgs = ['descripcion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Botón -->
            <div class="col-span-12 flex justify-end mt-4"> 
                <button type="button" onclick="document.getElementById('modalNuevoCurso').close()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300 mr-2">Cancelar</button>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Registrar Curso
                </button>
            </div>
        </form>
    </div>
</dialog>
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/cursos/partials/modal-nuevo.blade.php ENDPATH**/ ?>