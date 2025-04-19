<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Coordinacion;
use Illuminate\Validation\Rule; // Asegúrate que esté importado

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador']);
    }

    public function index()
    {
        $users = User::with('coordinacion')->get();
        $coordinaciones = Coordinacion::orderBy('nombre')->get();
        return view('admin.usuarios.index', compact('users', 'coordinaciones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email',
            'rol' => 'required|in:administrador,coordinador,analista,instructor',
            'password' => 'required|string|min:8|confirmed',
            'coordinacion_id' => 'nullable|exists:coordinaciones,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $newUser = User::create([ // Usar una variable diferente para claridad
            'name' => $validated['name'],
            'email' => $validated['email'],
            'rol' => $validated['rol'],
            'coordinacion_id' => $validated['coordinacion_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active'),
        ]);

        if (function_exists('registrar_auditoria')) {
            registrar_auditoria("Usuario creado", "Se registró el usuario {$validated['email']} con rol {$validated['rol']}");
        } else {
            activity()->performedOn($newUser)->log("Usuario {$validated['email']} creado con rol {$validated['rol']}");
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }

    // Método edit original sin cambios
    public function edit(User $user)
    {
         return view('admin.usuarios.edit', compact('user'));
    }

    // --- MÉTODO UPDATE CON CORRECCIÓN FINAL ---
    public function update(Request $request, User $usuario) // La variable es $usuario
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'email' => [
                'required',
                'string',
                'email',
                'max:191',
                // --- CORREGIDO: Usar $usuario->id ---
                Rule::unique('users')->ignore($usuario->id),
            ],
            'rol' => 'required|string|in:administrador,coordinador,analista,instructor',
            'coordinacion_id' => 'nullable|exists:coordinaciones,id',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'sometimes|boolean',
        ]);

        // --- CORREGIDO: Usar $usuario en todas las asignaciones ---
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->rol = $request->rol;
        $usuario->coordinacion_id = $request->coordinacion_id ?? null;
        $usuario->is_active = $request->boolean('is_active');

        if ($request->filled('password')) {
            // --- CORREGIDO: Usar $usuario ---
            $usuario->password = Hash::make($request->password);
        }

        // --- CORREGIDO: Usar $usuario ---
        $usuario->save();

        // Auditoría
        if (function_exists('registrar_auditoria')) {
             // --- CORREGIDO: Usar $usuario ---
             registrar_auditoria("Actualización de usuario", "Se actualizó el usuario {$usuario->name} ({$usuario->email}) con el rol {$usuario->rol}");
        } else {
             activity()
                // --- CORREGIDO: Usar $usuario ---
                ->performedOn($usuario)
                ->causedBy(auth()->user())
                // --- CORREGIDO: Usar $usuario ---
                ->log("Usuario {$usuario->name} actualizado");
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }


    // --- MÉTODO DESTROY (Usa $user porque no se cambió la firma) ---
    public function destroy(User $user) // Aquí la variable se llama $user
    {
        $userName = $user->name;
        $user->delete(); // Usar $user

        if (function_exists('registrar_auditoria')) {
             registrar_auditoria("Eliminación de usuario", "Se eliminó el usuario {$userName}");
        } else {
             activity()->causedBy(auth()->user())->log("Usuario {$userName} eliminado");
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    // --- OTROS MÉTODOS (Asegurarse de que usan $user si la firma usa $user) ---
    public function updateRole(Request $request, User $user) // Aquí es $user
    {
         $request->validate([ /* ... */ ]);
         $user->rol = $request->rol; // Usar $user
         $user->save(); // Usar $user
         return redirect()->route('admin.users.index')->with('success', 'Rol actualizado exitosamente.');
    }

    public function resetPassword(User $user) // Aquí es $user
    {
        $newPassword = 'password123';
        $user->update([ // Usar $user
            'password' => Hash::make($newPassword),
        ]);
        return redirect()->route('admin.users.index')->with('success', 'Contraseña reseteada exitosamente.');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
