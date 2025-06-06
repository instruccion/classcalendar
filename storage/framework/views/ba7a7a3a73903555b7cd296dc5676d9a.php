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
        <h2 class="text-xl font-semibold text-gray-800">📅 Gestión de Días Feriados</h2>
     <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-4xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Listado de Feriados</h1>
            <button onclick="abrirModalFeriado()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ➕ Nuevo Feriado
            </button>
        </div>

        <div class="bg-white rounded shadow p-4 overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Título</th>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">Recurrente</th>
                        <th class="px-4 py-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $feriados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feriado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-2"><?php echo e($feriado->titulo); ?></td>
                            <td class="px-4 py-2"><?php echo e(\Carbon\Carbon::parse($feriado->fecha)->format('d/m/Y')); ?></td>
                            <td class="px-4 py-2"><?php echo e($feriado->recurrente ? 'Sí' : 'No'); ?></td>
                            <td class="px-4 py-2 flex gap-4">
                            <?php
                                $feriadoData = [
                                    'id' => $feriado->id,
                                    'titulo' => $feriado->titulo,
                                    'fecha' => $feriado->fecha->format('Y-m-d'),
                                    'recurrente' => $feriado->recurrente,
                                ];
                            ?>
                            <button onclick='editarFeriado(<?php echo json_encode($feriadoData, 15, 512) ?>)' class="text-blue-600 hover:underline">Editar</button>

                                <form action="<?php echo e(route('admin.feriados.destroy', $feriado)); ?>" method="POST" onsubmit="return confirm('¿Eliminar este feriado?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-gray-500 py-4">No hay feriados registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <dialog id="modalFeriado" class="w-full max-w-xl p-0 rounded shadow-lg backdrop:bg-black/30">
        <div class="bg-white p-6">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h3 class="text-lg font-bold" id="modalTituloFeriado">Registrar Feriado</h3>
                <button onclick="document.getElementById('modalFeriado').close()" class="text-gray-600 hover:text-black text-xl">&times;</button>
            </div>
            <form method="POST" id="formFeriado" action="<?php echo e(route('admin.feriados.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block font-semibold mb-1">Título</label>
                    <input type="text" name="titulo" id="titulo" required maxlength="191" class="w-full border px-4 py-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Fecha</label>
                    <input type="date" name="fecha" id="fecha" required class="w-full border px-4 py-2 rounded">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="recurrente" id="recurrente" value="1">
                    <label for="recurrente" class="font-medium">¿Es feriado recurrente cada año?</label>
                </div>

                <div class="text-center pt-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Guardar Feriado
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <?php if(session('toast')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Livewire?.emit?.('toast', {
                    type: '<?php echo e(session('toast.type')); ?>',
                    message: '<?php echo e(session('toast.message')); ?>'
                });
            });
        </script>
    <?php endif; ?>

    <script>
        function abrirModalFeriado() {
            const form = document.getElementById('formFeriado');
            form.reset();
            form.action = "<?php echo e(route('admin.feriados.store')); ?>";

            // Elimina el campo _method si existe
            const existingMethod = document.getElementById('_methodFeriado');
            if (existingMethod) existingMethod.remove();

            // Siempre se agrega como POST para nuevo
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'POST';
            method.id = '_methodFeriado';
            form.appendChild(method);

            document.getElementById('modalTituloFeriado').textContent = 'Registrar Feriado';
            document.getElementById('modalFeriado').showModal();
        }

        function editarFeriado(data) {
            const form = document.getElementById('formFeriado');
            form.titulo.value = data.titulo;
            form.fecha.value = data.fecha;
            form.action = "<?php echo e(url('admin/feriados')); ?>/" + data.id;

            // Elimina el campo _method si ya existe
            const existingMethod = document.getElementById('_methodFeriado');
            if (existingMethod) existingMethod.remove();

            // Agrega campo hidden para PUT
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PUT';
            method.id = '_methodFeriado';
            form.appendChild(method);

            document.getElementById('recurrente').checked = !!data.recurrente;
            document.getElementById('modalTituloFeriado').textContent = 'Editar Feriado';
            document.getElementById('modalFeriado').showModal();
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
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/admin/feriados/index.blade.php ENDPATH**/ ?>