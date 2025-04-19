<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Coordinacion;
use Illuminate\Validation\Rule; // <-- Importar Rule para la validación unique

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador']);
    }

    public function index()
    {
        // Cargar usuarios con sus coordinaciones para evitar N+1 en la vista
        $users = User::with('coordinacion')->get();
        $coordinaciones = Coordinacion::orderBy('nombre')->get();

        return view('admin.usuarios.index', compact('users', 'coordinaciones'));
    }

    public function store(Request $request)
    {
        // Validación para crear (email debe ser único en toda la tabla)
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email',
            'rol' => 'required|in:administrador,coordinador,analista,instructor',
            'password' => 'required|string|min:8|confirmed',
            'coordinacion_id' => 'nullable|exists:coordinaciones,id',
            // En la creación, 'sometimes' es mejor si el campo puede no estar presente
            'is_active' => 'sometimes|boolean',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'rol' => $validated['rol'],
            'coordinacion_id' => $validated['coordinacion_id'] ?? null,
            'password' => Hash::make($validated['password']),
             // Usar boolean() es más robusto para checkboxes
            'is_active' => $request->boolean('is_active'),
        ]);

        // Asegúrate de que esta función exista o reemplázala por logging estándar/Spatie
        if (function_exists('registrar_auditoria')) {
            registrar_auditoria("Usuario creado", "Se registró el usuario {$validated['email']} con rol {$validated['rol']}");
        } else {
            // Alternativa con Spatie Logger (si lo usas)
            activity()
               ->log("Usuario {$validated['email']} creado con rol {$validated['rol']}");
        }


        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }


    // El método edit no necesita cambios aquí, asume que recibe el usuario por RBM
    public function edit(User $user)
    {
        // Deberías pasar también las coordinaciones si las muestras en un formulario de edición separado
        // $coordinaciones = Coordinacion::orderBy('nombre')->get();
        // return view('admin.usuarios.edit', compact('user', 'coordinaciones'));

        // Como estás usando modal, este método 'edit' probablemente no se usa para mostrar el form
        // pero lo dejamos por completitud si existiera la ruta admin.users.edit
         return view('admin.usuarios.edit', compact('user')); // Asume que tienes esta vista separada
    }

    public function update(Request $request, User $user) // $user es inyectado por Route Model Binding
    {
        // --- VALIDACIÓN CORREGIDA PARA ACTUALIZAR ---
        $request->validate([
            'name' => 'required|string|max:191',
            // Regla unique para email IGNORANDO el ID del usuario actual
            'email' => [
                'required',
                'string',
                'email',
                'max:191',
                Rule::unique('users')->ignore($user->id), // Forma recomendada
                // Alternativa: 'unique:users,email,' . $user->id, (funciona igual)
            ],
            'rol' => 'required|string|in:administrador,coordinador,analista,instructor',
            'coordinacion_id' => 'nullable|exists:coordinaciones,id', // Asegúrate que existe en tabla coordinaciones
            'password' => 'nullable|string|min:8|confirmed', // Opcional: solo si se llena
             // 'sometimes' porque si el checkbox no se marca, no vendrá en el request
             // 'boolean' para asegurar que el valor sea tratado como 0 o 1
            'is_active' => 'sometimes|boolean',
        ]);

        // Asignación de datos al modelo $user existente
        $user->name = $request->name;
        $user->email = $request->email;
        $user->rol = $request->rol;
        $user->coordinacion_id = $request->coordinacion_id ?? null; // Asigna null si viene vacío

        // --- MANEJO CORREGIDO DE 'is_active' ---
        // Si el checkbox 'is_active' está marcado, $request->boolean('is_active') será true (1)
        // Si no está marcado (y por tanto no se envía), será false (0)
        $user->is_active = $request->boolean('is_active');

        // Actualizar contraseña solo si se proporcionó una nueva
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Guardar los cambios (esto ejecutará un UPDATE porque $user existe)
        $user->save();

        // Auditoría (asegúrate que la función exista)
        if (function_exists('registrar_auditoria')) {
             registrar_auditoria("Actualización de usuario", "Se actualizó el usuario {$user->name} ({$user->email}) con el rol {$user->rol}");
        } else {
             // Alternativa con Spatie Logger
             activity()
                ->performedOn($user)
                ->causedBy(auth()->user()) // Opcional: registrar quién hizo el cambio
                ->log("Usuario {$user->name} actualizado");
        }

        // Redirigir de vuelta al índice con mensaje de éxito
        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }


    public function destroy(User $user)
    {
        $userName = $user->name; // Guardar nombre para el log
        $user->delete();

        // Auditoría (asegúrate que la función exista)
         if (function_exists('registrar_auditoria')) {
             registrar_auditoria("Eliminación de usuario", "Se eliminó el usuario {$userName}");
        } else {
             // Alternativa con Spatie Logger
             activity()
                ->causedBy(auth()->user())
                ->log("Usuario {$userName} eliminado");
        }


        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    // --- OTROS MÉTODOS (SIN CAMBIOS IMPORTANTES NECESARIOS PARA ESTE ERROR) ---
    public function updateRole(Request $request, User $user)
    {
        // ... (validación y lógica original) ...
         $request->validate([
            'rol' => 'required|string|in:administrador,coordinador,analista,instructor',
        ]);
        $user->rol = $request->rol;
        $user->save();
        // Auditoría?
        return redirect()->route('admin.users.index')->with('success', 'Rol actualizado exitosamente.');
    }

    public function resetPassword(User $user)
    {
         // ... (lógica original) ...
        $newPassword = 'password123'; // O genera una aleatoria segura
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
         // Auditoría? (Quizás registrar que se reseteó, pero no la contraseña)
        return redirect()->route('admin.users.index')->with('success', 'Contraseña reseteada exitosamente.');
    }

    public function logout(Request $request)
    {
         // ... (lógica original) ...
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
