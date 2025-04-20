<!DOCTYPE html>

<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class=""> 
<head>
    <meta charset="utf-8">
    <title><?php echo e(config('app.name', 'CursosLaser')); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.3.67/css/materialdesignicons.min.css">

    
    <style>
        /* En desktop, cuando el sidebar está colapsado, quitar margen */
        .sidebar-collapsed #main-content {
            margin-left: 0 !important; /* !important puede ser necesario para sobreescribir ml-64 */
        }
        /* En desktop, cuando el sidebar está colapsado, ocultar el sidebar */
        /* Usar transform para permitir transición */
        .sidebar-collapsed #sidebar {
             transform: translateX(-100%);
        }

        /* Asegurar que en pantallas pequeñas el margen siempre sea 0 */
        @media (max-width: 1023px) {
            #main-content {
                margin-left: 0 !important;
            }
             /* Ocultar sidebar inicialmente en móvil, pero permitir mostrarlo */
             /* #sidebar {
                transform: translateX(-100%);
             }
             #sidebar:not(.hidden) {
                transform: translateX(0);
             } */
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800">

    
    <header class="fixed top-0 left-0 right-0 bg-white shadow-md h-16 flex items-center justify-between px-4 z-50">
        
        <div class="flex items-center gap-2">
            <button id="menu-toggle" class="w-10 h-10 rounded-full border flex items-center justify-center hover:bg-gray-100 lg:hidden"> 
                <i class="mdi mdi-menu text-xl"></i>
            </button>
             <button id="desktop-menu-toggle" class="w-10 h-10 rounded-full border items-center justify-center hover:bg-gray-100 hidden lg:flex"> 
                <i class="mdi mdi-menu text-xl"></i>
            </button>
        </div>

        
        <div class="absolute left-1/2 transform -translate-x-1/2">
             
            <img src="<?php echo e(asset('assets/images/logo-light.png')); ?>" alt="Logo" class="h-8">
        </div>

        
        <div class="flex items-center gap-4">
            
            <button id="btn-notificaciones" class="w-10 h-10 rounded-full border flex items-center justify-center hover:bg-gray-100 relative">
                <i class="mdi mdi-bell-outline text-xl"></i>
                <span id="contador-mensajes" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 rounded-full font-semibold hidden">0</span>
            </button>

            
            <button id="btnFullscreen" class="w-10 h-10 rounded-full border flex items-center justify-center hover:bg-gray-100">
                <i class="mdi mdi-fullscreen text-xl"></i>
            </button>

            
            <div class="relative">
                <div class="flex items-center gap-3 cursor-pointer" id="avatar-btn">
                    <div class="text-right hidden md:block leading-tight">
                        
                        <div class="text-gray-800 font-semibold"><?php echo e(Auth::user()->name); ?></div>
                        <?php
                            // RECOMENDACIÓN: Mover a accesor User->formatted_role_name
                            $rolNombre = ucfirst(Auth::user()->rol ?? 'Usuario');
                        ?>
                        <div class="text-xs text-gray-500"><?php echo e($rolNombre); ?></div>
                    </div>
                    <?php
                         // RECOMENDACIÓN: Mover a accesor User->profile_photo_url
                        $foto = Auth::user()->foto ?? null;
                        $fotoPerfil = $foto && file_exists(public_path("uploads/$foto"))
                                        ? asset("uploads/$foto")
                                        : asset("assets/images/users/avatar-default.png");
                    ?>
                    <img src="<?php echo e($fotoPerfil); ?>" class="w-10 h-10 rounded-full object-cover border" alt="Avatar">
                </div>

                
                <div id="avatar-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded shadow-lg z-50 py-1">
                    <a href="<?php echo e(route('profile.edit')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="block">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Salir</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    
    
    <aside id="sidebar"
        class="fixed top-16 left-0 bottom-0 w-64 bg-white border-r border-gray-300 p-4 z-40 transition-transform duration-300 ease-in-out transform hidden lg:block lg:translate-x-0">
        <h2 class="text-xl font-bold mb-6">Menú</h2>
        <nav class="flex flex-col gap-3 text-sm">
            
            <a href="<?php echo e(route('calendario.index')); ?>" class="hover:underline text-gray-800">📅 Calendario</a>
            <a href="<?php echo e(route('admin.programaciones.index')); ?>" class="hover:underline text-gray-800">📦 Programaciones</a>
            <a href="<?php echo e(route('agenda.index')); ?>" class="hover:underline text-gray-800">🗓️ Agenda</a>
            <a href="<?php echo e(route('admin.grupos.index')); ?>" class="hover:underline text-gray-800"> 👥 Grupos </a>
            <a href="<?php echo e(route('admin.cursos.index')); ?>" class="hover:underline text-gray-800">📘 Cursos</a>
            <a href="<?php echo e(route('admin.aulas.index')); ?>" class="hover:underline text-gray-800">🏫 Aulas</a>
            <a href="<?php echo e(route('admin.instructores.index')); ?>" class="hover:underline text-gray-800">🧑‍🏫 Instructores</a>


             
            <?php if(auth()->user()?->rol === 'administrador'): ?>
                <a href="<?php echo e(route('admin.coordinaciones.index')); ?>" class="hover:underline text-gray-800">📍 Coordinaciones</a>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="hover:underline text-gray-800">👤 Usuarios</a>
                <a href="<?php echo e(route('admin.feriados.index')); ?>" class="hover:underline text-gray-800">📅 Días Feriados</a>
                <a href="<?php echo e(route('admin.auditorias.index')); ?>" class="hover:underline text-gray-800">📋 Auditorías</a>
            <?php endif; ?>
        </nav>
    </aside>

    
    <div id="sidebar-overlay"
        class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm z-30 hidden lg:hidden transition-opacity duration-300 ease-in-out opacity-0"> 
    </div>

    
    
    <main id="main-content"
        class="mt-16 p-6 min-h-[calc(100vh-8rem)] pb-20 transition-all duration-300 ease-in-out lg:ml-64">
        <!-- Alertas del sistema -->

        <?php echo e($slot); ?>


        <?php if(session('success')): ?>
            <?php if (isset($component)) { $__componentOriginal7cfab914afdd05940201ca0b2cbc009b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast','data' => ['type' => 'success','message' => session('success')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'success','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('success'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $attributes = $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $component = $__componentOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <?php if (isset($component)) { $__componentOriginal7cfab914afdd05940201ca0b2cbc009b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast','data' => ['type' => 'error','message' => session('error')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'error','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('error'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $attributes = $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $component = $__componentOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginal7cfab914afdd05940201ca0b2cbc009b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast','data' => ['type' => 'error','message' => $error]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'error','message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($error)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $attributes = $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $component = $__componentOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>


    </main>

    
    <footer class="fixed bottom-0 left-0 right-0 bg-white text-center text-sm py-2 border-t z-10">
        2025 © CursosLaser | Desarrollado por De Gouveia José
    </footer>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <?php if(session('toast')): ?>
        <?php
            $toastType = session('toast.type') ?? 'info';
            $toastColor = match($toastType) {
                'success' => 'bg-green-600',
                'error' => 'bg-red-600',
                'warning' => 'bg-yellow-600',
                default => 'bg-blue-600',
            };
        ?>

        <div x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            class="fixed top-5 right-5 text-white px-4 py-2 rounded shadow z-50 transition <?php echo e($toastColor); ?>">
            <?php echo e(session('toast.message')); ?>

        </div>
    <?php endif; ?>

    <script>
        // Listener global para cerrar modales con $dispatch('close-dialog', 'modalID')
        window.addEventListener('close-dialog', event => {
            const id = event.detail;
            const dialog = document.getElementById(id);
            if (dialog && typeof dialog.close === 'function') {
                dialog.close();
            } else {
                console.warn(`No se pudo cerrar el dialog con ID: ${id}`);
            }
        });
    </script>



</body>
</html>
<?php /**PATH C:\wamp64\www\cursoslaser\resources\views/layouts/app.blade.php ENDPATH**/ ?>