<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Usuarios Registrados
        </h2>
    </x-slot>

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
                        <th class="px-4 py-2">Coordinación</th> {{-- Añadida columna Coordinación --}}
                        <th class="px-4 py-2">Activo</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Cargar coordinación para mostrarla --}}
                    @foreach ($users->load('coordinacion') as $user)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $user->id }}</td>
                            <td class="px-4 py-2">{{ $user->name }}</td>
                            <td class="px-4 py-2">{{ $user->email }}</td>
                            <td class="px-4 py-2">{{ ucfirst($user->rol) }}</td>
                            <td class="px-4 py-2">{{ $user->coordinacion->nombre ?? 'N/A' }}</td> {{-- Mostrar nombre --}}
                            <td class="px-4 py-2">
                                <span class="{{ $user->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 flex gap-2 flex-wrap"> {{-- Añadido flex-wrap --}}
                                <!-- Botón de Editar (con Modal) -->
                                {{-- Pasar todo el objeto usuario como JSON para facilitar el llenado --}}
                                <button onclick='openEditModal(@json($user))' class="text-blue-600 hover:underline">Editar</button>

                                <!-- Eliminar -->
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Confirmas la eliminación del usuario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>

                            </td>
                        </tr>
                    @endforeach
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

                {{-- Mostrar errores si existen al recargar --}}
                @if ($errors->any() && old('_form_marker') === 'create')
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <strong>Se encontraron los siguientes errores:</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Formulario -->
                <form id="createUserForm" method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_form_marker" value="create"> {{-- Marcador para saber qué modal tenía error --}}

                    {{-- CAMPOS DEL FORMULARIO DE CREACIÓN (igual que antes) --}}
                    <!-- Nombre -->
                    <div>
                        <label for="createName" class="block text-sm font-medium {{ $errors->has('name') && old('_form_marker') === 'create' ? 'text-red-600' : 'text-gray-700' }} mb-1">Nombre</label>
                        <input type="text" id="createName" name="name" value="{{ old('name') }}" required
                            class="w-full px-3 py-2 border {{ $errors->has('name') && old('_form_marker') === 'create' ? 'border-red-500' : 'border-gray-300' }} rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name')
                            @if(old('_form_marker') === 'create') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @endif
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="createEmail" class="block text-sm font-medium {{ $errors->has('email') && old('_form_marker') === 'create' ? 'text-red-600' : 'text-gray-700' }} mb-1">Email</label>
                        <input type="email" id="createEmail" name="email" value="{{ old('email') }}" required
                            class="w-full px-3 py-2 border {{ $errors->has('email') && old('_form_marker') === 'create' ? 'border-red-500' : 'border-gray-300' }} rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('email')
                             @if(old('_form_marker') === 'create') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @endif
                        @enderror
                    </div>

                    <!-- Rol -->
                    <div>
                        <label for="createRol" class="block text-sm font-medium {{ $errors->has('rol') && old('_form_marker') === 'create' ? 'text-red-600' : 'text-gray-700' }} mb-1">Rol</label>
                        <select id="createRol" name="rol" required class="w-full px-3 py-2 border {{ $errors->has('rol') && old('_form_marker') === 'create' ? 'border-red-500' : 'border-gray-300' }} rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="" disabled {{ old('rol') ? '' : 'selected' }}>Seleccione un rol</option>
                            <option value="administrador" {{ old('rol') == 'administrador' ? 'selected' : '' }}>Administrador</option>
                            <option value="coordinador" {{ old('rol') == 'coordinador' ? 'selected' : '' }}>Coordinador</option>
                            <option value="analista" {{ old('rol') == 'analista' ? 'selected' : '' }}>Analista</option>
                            <option value="instructor" {{ old('rol') == 'instructor' ? 'selected' : '' }}>Instructor</option>
                        </select>
                        @error('rol')
                            @if(old('_form_marker') === 'create') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @endif
                        @enderror
                    </div>

                    <!-- Coordinación (Solo Admin) -->
                    @if(Auth::user()->rol === 'administrador')
                    <div>
                        <label for="createCoordinacion" class="block text-sm font-medium {{ $errors->has('coordinacion_id') && old('_form_marker') === 'create' ? 'text-red-600' : 'text-gray-700' }} mb-1">Coordinación (Opcional)</label>
                        <select id="createCoordinacion" name="coordinacion_id" class="w-full px-3 py-2 border {{ $errors->has('coordinacion_id') && old('_form_marker') === 'create' ? 'border-red-500' : 'border-gray-300' }} rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Ninguna</option>
                            @foreach ($coordinaciones as $coordinacion)
                                <option value="{{ $coordinacion->id }}" {{ old('coordinacion_id') == $coordinacion->id ? 'selected' : '' }}>{{ $coordinacion->nombre }}</option>
                            @endforeach
                        </select>
                        @error('coordinacion_id')
                             @if(old('_form_marker') === 'create') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @endif
                        @enderror
                    </div>
                    @endif

                    <!-- Contraseña -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="createPassword" class="block text-sm font-medium {{ $errors->has('password') && old('_form_marker') === 'create' ? 'text-red-600' : 'text-gray-700' }} mb-1">Contraseña</label>
                            <input type="password" id="createPassword" name="password" required class="w-full px-3 py-2 border {{ $errors->has('password') && old('_form_marker') === 'create' ? 'border-red-500' : 'border-gray-300' }} rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('password')
                                @if(old('_form_marker') === 'create') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @endif
                            @enderror
                        </div>
                        <div>
                            <label for="createPasswordConfirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                            <input type="password" id="createPasswordConfirmation" name="password_confirmation" required class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Activo -->
                    <div class="flex items-center">
                        <input type="checkbox" id="createIsActive" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="createIsActive" class="ml-2 block text-sm text-gray-900">Usuario Activo</label>
                    </div>
                    {{-- FIN CAMPOS FORMULARIO CREACIÓN --}}


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

                {{-- Mostrar errores si existen al recargar --}}
                 @if ($errors->any() && old('_form_marker') === 'edit')
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <strong>Se encontraron los siguientes errores:</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- El action se establecerá con Javascript --}}
                <form id="editUserForm" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editUserId" name="user_id_field"> {{-- Campo oculto con nombre diferente para evitar conflicto si se usa user_id en validación --}}
                    <input type="hidden" name="_form_marker" value="edit"> {{-- Marcador para saber qué modal tenía error --}}


                    <!-- Nombre -->
                    <div>
                        <label for="editName" class="block text-sm font-medium {{ $errors->has('name') && old('_form_marker') === 'edit' ? 'text-red-600' : 'text-gray-700' }} mb-1">Nombre</label>
                        <input type="text" id="editName" name="name" required
                               class="w-full px-3 py-2 border {{ $errors->has('name') && old('_form_marker') === 'edit' ? 'border-red-500' : 'border-gray-300' }} rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name')
                           @if(old('_form_marker') === 'edit') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @endif
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="editEmail" class="block text-sm font-medium {{ $errors->has('email') && old('_form_marker') === 'edit' ? 'text-red-600' : 'text-gray-700' }} mb-1">Email</label>
                        <input type="email" id="editEmail" name="email" required
                               class="w-full px-3 py-2 border {{ $errors->has('email') && old('_form_marker') === 'edit' ? 'border-red-500' : 'border-gray-300' }} rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                         @error('email')
                           @if(old('_form_marker') === 'edit') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @endif
                        @enderror
                    </div>

                    <!-- Rol -->
                    <div>
                        <label for="editRol" class="block text-sm font-medium {{ $errors->has('rol') && old('_form_marker') === 'edit' ? 'text-red-600' : 'text-gray-700' }} mb-1">Rol</label>
                        <select id="editRol" name="rol" required
                                class="w-full px-3 py-2 border {{ $errors->has('rol') && old('_form_marker') === 'edit' ? 'border-red-500' : 'border-gray-300' }} rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="administrador">Administrador</option>
                            <option value="coordinador">Coordinador</option>
                            <option value="analista">Analista</option>
                            <option value="instructor">Instructor</option>
                        </select>
                         @error('rol')
                           @if(old('_form_marker') === 'edit') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @endif
                        @enderror
                    </div>

                    <!-- Coordinación (Solo Admin) -->
                    @if (auth()->user()->rol === 'administrador')
                        <div>
                            <label for="editCoordinacion" class="block text-sm font-medium {{ $errors->has('coordinacion_id') && old('_form_marker') === 'edit' ? 'text-red-600' : 'text-gray-700' }} mb-1">Coordinación (Opcional)</label>
                            <select id="editCoordinacion" name="coordinacion_id"
                                    class="w-full px-3 py-2 border {{ $errors->has('coordinacion_id') && old('_form_marker') === 'edit' ? 'border-red-500' : 'border-gray-300' }} rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Ninguna</option> {{-- Opción para desasignar --}}
                                @foreach ($coordinaciones as $coordinacion)
                                    <option value="{{ $coordinacion->id }}">{{ $coordinacion->nombre }}</option>
                                @endforeach
                            </select>
                            @error('coordinacion_id')
                                @if(old('_form_marker') === 'edit') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @endif
                            @enderror
                        </div>
                    @endif

                    <!-- Activo (checkbox) -->
                    <div class="flex items-center">
                        <input type="checkbox" id="editIsActive" name="is_active" value="1"
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="editIsActive" class="ml-2 block text-sm text-gray-900">Usuario Activo</label>
                    </div>

                    <!-- Nueva Contraseña -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="editPassword" class="block text-sm font-medium {{ $errors->has('password') && old('_form_marker') === 'edit' ? 'text-red-600' : 'text-gray-700' }} mb-1">Nueva Contraseña (opcional)</label>
                            <input type="password" id="editPassword" name="password"
                                   class="w-full px-3 py-2 border {{ $errors->has('password') && old('_form_marker') === 'edit' ? 'border-red-500' : 'border-gray-300' }} rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Dejar vacío para no cambiar">
                            @error('password')
                                @if(old('_form_marker') === 'edit') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @endif
                            @enderror
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
            const baseUrl = @json(rtrim(url('admin/usuarios'), '/'));
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
            @if ($errors->any())
                var formMarker = "{{ old('_form_marker') }}";
                if (formMarker === 'create') {
                    console.log("Reabriendo modal de creación debido a errores.");
                    openCreateModal();
                } else if (formMarker === 'edit') {
                    // Para reabrir el de edición, necesitaríamos el ID del usuario que falló.
                    // Esto es más complejo. Podríamos pasar el ID en la sesión flash o en old().
                    // Por simplicidad, podríamos solo mostrar un error general o no reabrirlo automáticamente.
                    // O, si el user_id se envió, podríamos usar old('user_id_field') para llamar a openEditModal
                    // con los datos correctos (requeriría buscar el user en @json($users) usando old('user_id_field'))
                    console.log("Errores detectados en el formulario de edición (modal no reabierto automáticamente). ID del usuario con error:", "{{ old('user_id_field') }}");
                    // Opcional: Podrías intentar reabrir si tienes el ID
                    const failedUserId = parseInt("{{ old('user_id_field') }}", 10);
                    if (!isNaN(failedUserId)) {
                         const usersData = @json($users);
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
            @endif
        });
    </script>
</x-app-layout>
