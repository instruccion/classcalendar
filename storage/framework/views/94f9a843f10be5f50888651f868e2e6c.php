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
            Usuarios Registrados
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-4 max-w-7xl mx-auto">
        <!-- Botón para agregar un nuevo usuario -->
        <button onclick="openCreateModal()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mb-4">
            ➕ Nuevo Usuario
        </button>

        <!-- Tabla de Usuarios -->
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Nombre</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Rol</th>
                        <th class="px-4 py-2">Coordinación</th> 
                        <th class="px-4 py-2">Activo</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <?php $__currentLoopData = $users->load('coordinacion'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?php echo e($user->id); ?></td>
                            <td class="px-4 py-2"><?php echo e($user->name); ?></td>
                            <td class="px-4 py-2"><?php echo e($user->email); ?></td>
                            <td class="px-4 py-2"><?php echo e(ucfirst($user->rol)); ?></td>
                            <td class="px-4 py-2"><?php echo e($user->coordinacion->nombre ?? 'N/A'); ?></td> 
                            <td class="px-4 py-2">
                                <span class="<?php echo e($user->is_active ? 'text-green-600' : 'text-red-600'); ?>">
                                    <?php echo e($user->is_active ? 'Activo' : 'Inactivo'); ?>

                                </span>
                            </td>
                            <td class="px-4 py-2 flex gap-2 flex-wrap"> 
                                <!-- Botón de Editar (con Modal) -->
                                
                                <button onclick='openEditModal(<?php echo json_encode($user, 15, 512) ?>)' class="text-blue-600 hover:underline">Editar</button>

                                <!-- Eliminar -->
                                <form action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST" onsubmit="return confirm('¿Confirmas la eliminación del usuario?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <!-- Modal de Crear Usuario -->
        <dialog id="createUserModal" class="w-full max-w-lg p-0 rounded-lg shadow-lg backdrop:bg-black/30">
            <div class="bg-white p-6">
                <!-- Encabezado -->
                <div class="flex items-center justify-between pb-3 border-b mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Crear Nuevo Usuario</h3>
                    <button onclick="closeCreateModal()" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">×</button>
                </div>

                
                <?php if($errors->any() && old('_form_marker') === 'create'): ?>
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <strong>Se encontraron los siguientes errores:</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Formulario -->
                <form id="createUserForm" method="POST" action="<?php echo e(route('admin.users.store')); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_form_marker" value="create"> 

                    
                    <!-- Nombre -->
                    <div>
                        <label for="createName" class="block text-sm font-medium <?php echo e($errors->has('name') && old('_form_marker') === 'create' ? 'text-red-600' : 'text-gray-700'); ?> mb-1">Nombre</label>
                        <input type="text" id="createName" name="name" value="<?php echo e(old('name')); ?>" required
                            class="w-full px-3 py-2 border <?php echo e($errors->has('name') && old('_form_marker') === 'create' ? 'border-red-500' : 'border-gray-300'); ?> rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <?php if(old('_form_marker') === 'create'): ?> <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p> <?php endif; ?>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="createEmail" class="block text-sm font-medium <?php echo e($errors->has('email') && old('_form_marker') === 'create' ? 'text-red-600' : 'text-gray-700'); ?> mb-1">Email</label>
                        <input type="email" id="createEmail" name="email" value="<?php echo e(old('email')); ?>" required
                            class="w-full px-3 py-2 border <?php echo e($errors->has('email') && old('_form_marker') === 'create' ? 'border-red-500' : 'border-gray-300'); ?> rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                             <?php if(old('_form_marker') === 'create'): ?> <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p> <?php endif; ?>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Rol -->
                    <div>
                        <label for="createRol" class="block text-sm font-medium <?php echo e($errors->has('rol') && old('_form_marker') === 'create' ? 'text-red-600' : 'text-gray-700'); ?> mb-1">Rol</label>
                        <select id="createRol" name="rol" required class="w-full px-3 py-2 border <?php echo e($errors->has('rol') && old('_form_marker') === 'create' ? 'border-red-500' : 'border-gray-300'); ?> rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="" disabled <?php echo e(old('rol') ? '' : 'selected'); ?>>Seleccione un rol</option>
                            <option value="administrador" <?php echo e(old('rol') == 'administrador' ? 'selected' : ''); ?>>Administrador</option>
                            <option value="coordinador" <?php echo e(old('rol') == 'coordinador' ? 'selected' : ''); ?>>Coordinador</option>
                            <option value="analista" <?php echo e(old('rol') == 'analista' ? 'selected' : ''); ?>>Analista</option>
                            <option value="instructor" <?php echo e(old('rol') == 'instructor' ? 'selected' : ''); ?>>Instructor</option>
                        </select>
                        <?php $__errorArgs = ['rol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <?php if(old('_form_marker') === 'create'): ?> <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p> <?php endif; ?>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Coordinación (Solo Admin) -->
                    <?php if(Auth::user()->rol === 'administrador'): ?>
                    <div>
                        <label for="createCoordinacion" class="block text-sm font-medium <?php echo e($errors->has('coordinacion_id') && old('_form_marker') === 'create' ? 'text-red-600' : 'text-gray-700'); ?> mb-1">Coordinación (Opcional)</label>
                        <select id="createCoordinacion" name="coordinacion_id" class="w-full px-3 py-2 border <?php echo e($errors->has('coordinacion_id') && old('_form_marker') === 'create' ? 'border-red-500' : 'border-gray-300'); ?> rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Ninguna</option>
                            <?php $__currentLoopData = $coordinaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coordinacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($coordinacion->id); ?>" <?php echo e(old('coordinacion_id') == $coordinacion->id ? 'selected' : ''); ?>><?php echo e($coordinacion->nombre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['coordinacion_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                             <?php if(old('_form_marker') === 'create'): ?> <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p> <?php endif; ?>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Contraseña -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="createPassword" class="block text-sm font-medium <?php echo e($errors->has('password') && old('_form_marker') === 'create' ? 'text-red-600' : 'text-gray-700'); ?> mb-1">Contraseña</label>
                            <input type="password" id="createPassword" name="password" required class="w-full px-3 py-2 border <?php echo e($errors->has('password') && old('_form_marker') === 'create' ? 'border-red-500' : 'border-gray-300'); ?> rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <?php if(old('_form_marker') === 'create'): ?> <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p> <?php endif; ?>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label for="createPasswordConfirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                            <input type="password" id="createPasswordConfirmation" name="password_confirmation" required class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Activo -->
                    <div class="flex items-center">
                        <input type="checkbox" id="createIsActive" name="is_active" value="1" <?php echo e(old('is_active', true) ? 'checked' : ''); ?>

                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="createIsActive" class="ml-2 block text-sm text-gray-900">Usuario Activo</label>
                    </div>
                    


                    <!-- Botones -->
                    <div class="flex justify-end pt-4 space-x-2 border-t mt-4">
                        <button type="button" onclick="closeCreateModal()"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                            Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </dialog>



        <!-- Modal de Editar Usuario -->
        <dialog id="editUserModal" class="w-full max-w-lg p-0 rounded-lg shadow-lg backdrop:bg-black/30">
            <div class="bg-white p-6">
                <div class="flex items-center justify-between pb-3 border-b mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Editar Usuario</h3>
                    <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">×</button>
                </div>

                
                 <?php if($errors->any() && old('_form_marker') === 'edit'): ?>
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <strong>Se encontraron los siguientes errores:</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                
                <form id="editUserForm" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <input type="hidden" id="editUserId" name="user_id_field"> 
                    <input type="hidden" name="_form_marker" value="edit"> 


                    <!-- Nombre -->
                    <div>
                        <label for="editName" class="block text-sm font-medium <?php echo e($errors->has('name') && old('_form_marker') === 'edit' ? 'text-red-600' : 'text-gray-700'); ?> mb-1">Nombre</label>
                        <input type="text" id="editName" name="name" required
                               class="w-full px-3 py-2 border <?php echo e($errors->has('name') && old('_form_marker') === 'edit' ? 'border-red-500' : 'border-gray-300'); ?> rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                           <?php if(old('_form_marker') === 'edit'): ?> <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p> <?php endif; ?>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="editEmail" class="block text-sm font-medium <?php echo e($errors->has('email') && old('_form_marker') === 'edit' ? 'text-red-600' : 'text-gray-700'); ?> mb-1">Email</label>
                        <input type="email" id="editEmail" name="email" required
                               class="w-full px-3 py-2 border <?php echo e($errors->has('email') && old('_form_marker') === 'edit' ? 'border-red-500' : 'border-gray-300'); ?> rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                         <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                           <?php if(old('_form_marker') === 'edit'): ?> <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p> <?php endif; ?>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Rol -->
                    <div>
                        <label for="editRol" class="block text-sm font-medium <?php echo e($errors->has('rol') && old('_form_marker') === 'edit' ? 'text-red-600' : 'text-gray-700'); ?> mb-1">Rol</label>
                        <select id="editRol" name="rol" required
                                class="w-full px-3 py-2 border <?php echo e($errors->has('rol') && old('_form_marker') === 'edit' ? 'border-red-500' : 'border-gray-300'); ?> rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="administrador">Administrador</option>
                            <option value="coordinador">Coordinador</option>
                            <option value="analista">Analista</option>
                            <option value="instructor">Instructor</option>
                        </select>
                         <?php $__errorArgs = ['rol'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                           <?php if(old('_form_marker') === 'edit'): ?> <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p> <?php endif; ?>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Coordinación (Solo Admin) -->
                    <?php if(auth()->user()->rol === 'administrador'): ?>
                        <div>
                            <label for="editCoordinacion" class="block text-sm font-medium <?php echo e($errors->has('coordinacion_id') && old('_form_marker') === 'edit' ? 'text-red-600' : 'text-gray-700'); ?> mb-1">Coordinación (Opcional)</label>
                            <select id="editCoordinacion" name="coordinacion_id"
                                    class="w-full px-3 py-2 border <?php echo e($errors->has('coordinacion_id') && old('_form_marker') === 'edit' ? 'border-red-500' : 'border-gray-300'); ?> rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Ninguna</option> 
                                <?php $__currentLoopData = $coordinaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coordinacion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($coordinacion->id); ?>"><?php echo e($coordinacion->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['coordinacion_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <?php if(old('_form_marker') === 'edit'): ?> <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p> <?php endif; ?>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Activo (checkbox) -->
                    <div class="flex items-center">
                        <input type="checkbox" id="editIsActive" name="is_active" value="1"
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="editIsActive" class="ml-2 block text-sm text-gray-900">Usuario Activo</label>
                    </div>

                    <!-- Nueva Contraseña -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="editPassword" class="block text-sm font-medium <?php echo e($errors->has('password') && old('_form_marker') === 'edit' ? 'text-red-600' : 'text-gray-700'); ?> mb-1">Nueva Contraseña (opcional)</label>
                            <input type="password" id="editPassword" name="password"
                                   class="w-full px-3 py-2 border <?php echo e($errors->has('password') && old('_form_marker') === 'edit' ? 'border-red-500' : 'border-gray-300'); ?> rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Dejar vacío para no cambiar">
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <?php if(old('_form_marker') === 'edit'): ?> <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p> <?php endif; ?>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                         <div>
                            <label for="editPasswordConfirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                            <input type="password" id="editPasswordConfirmation" name="password_confirmation"
                                   class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end pt-4 space-x-2 border-t mt-4">
                         <button type="button" onclick="closeEditModal()"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                            Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>

    <script>
        const createModal = document.getElementById('createUserModal');
        const editModal = document.getElementById('editUserModal');
        const createForm = document.getElementById('createUserForm');
        const editForm = document.getElementById('editUserForm');

        // --- Funciones para Modales ---
        function openCreateModal() {
            createForm.reset(); // Limpiar formulario al abrir
             // Limpiar errores visuales previos si los hubiera
            clearValidationErrors(createForm);
            createModal.showModal(); // Usar showModal() para dialog
        }

        function closeCreateModal() {
            createModal.close(); // Usar close() para dialog
        }

        function openEditModal(userData) { // Recibe el objeto user completo
             // Limpiar errores visuales previos si los hubiera
            clearValidationErrors(editForm);

            // --- CORRECCIÓN PRINCIPAL: Establecer el action del formulario ---
            const baseUrl = <?php echo json_encode(rtrim(url('admin/usuarios'), '/'), 512) ?>;
            const actionUrl = `${baseUrl}/${userData.id}`;
            editForm.setAttribute('action', actionUrl);
            console.log("Edit form action set to:", actionUrl); // Para depuración

            // Llenar el formulario con los datos del usuario
            document.getElementById('editUserId').value = userData.id; // Opcional si ID va en URL
            document.getElementById('editName').value = userData.name;
            document.getElementById('editEmail').value = userData.email;
            document.getElementById('editRol').value = userData.rol;

            // Manejar coordinación si existe el select (solo para admin)
            const editCoordinacionSelect = document.getElementById('editCoordinacion');
            if (editCoordinacionSelect) {
                editCoordinacionSelect.value = userData.coordinacion_id ?? ''; // Poner vacío si es null
            }

            document.getElementById('editIsActive').checked = userData.is_active == 1; // Asegurar comparación correcta

            // Limpiar campos de contraseña
            document.getElementById('editPassword').value = '';
            document.getElementById('editPasswordConfirmation').value = '';

            editModal.showModal(); // Usar showModal() para dialog
        }

        function closeEditModal() {
            editModal.close(); // Usar close() para dialog
        }

        // Helper para limpiar errores de validación visuales
        function clearValidationErrors(form) {
            form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
            form.querySelectorAll('.text-red-600').forEach(el => {
                // No eliminar labels, solo mensajes de error bajo los inputs
                if (el.tagName === 'P' && el.closest('div').querySelector('input, select')) {
                    el.remove();
                } else if (el.tagName === 'LABEL') {
                    el.classList.remove('text-red-600');
                    el.classList.add('text-gray-700');
                }
            });
            // Quitar el div de errores generales si existe
            form.closest('.bg-white').querySelector('.bg-red-100')?.remove();
        }


        // Reabrir el modal correcto si hubo errores de validación al recargar la página
        document.addEventListener("DOMContentLoaded", function () {
            <?php if($errors->any()): ?>
                var formMarker = "<?php echo e(old('_form_marker')); ?>";
                if (formMarker === 'create') {
                    console.log("Reabriendo modal de creación debido a errores.");
                    openCreateModal();
                } else if (formMarker === 'edit') {
                    // Para reabrir el de edición, necesitaríamos el ID del usuario que falló.
                    // Esto es más complejo. Podríamos pasar el ID en la sesión flash o en old().
                    // Por simplicidad, podríamos solo mostrar un error general o no reabrirlo automáticamente.
                    // O, si el user_id se envió, podríamos usar old('user_id_field') para llamar a openEditModal
                    // con los datos correctos (requeriría buscar el user en <?php echo json_encode($users, 15, 512) ?> usando old('user_id_field'))
                    console.log("Errores detectados en el formulario de edición (modal no reabierto automáticamente). ID del usuario con error:", "<?php echo e(old('user_id_field')); ?>");
                    // Opcional: Podrías intentar reabrir si tienes el ID
                    const failedUserId = parseInt("<?php echo e(old('user_id_field')); ?>", 10);
                    if (!isNaN(failedUserId)) {
                         const usersData = <?php echo json_encode($users, 15, 512) ?>;
                         const failedUserData = usersData.find(u => u.id === failedUserId);
                         if (failedUserData) {
                             console.log("Intentando reabrir modal de edición para usuario ID:", failedUserId);
                             openEditModal(failedUserData);
                             // NOTA: Los valores 'old()' para los campos individuales no se aplican automáticamente
                             // aquí porque llenamos desde 'failedUserData'. Se necesitaría lógica adicional
                             // para priorizar 'old()' si se quiere mantener lo que el usuario re-escribió.
                         }
                    }

                }
            <?php endif; ?>
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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/usuarios/index.blade.php ENDPATH**/ ?>