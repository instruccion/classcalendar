<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador']);
    }

    public function index()
    {
        $users = User::all(); // O puedes aplicar más lógica de filtrado si es necesario
        return view('admin.usuarios.index', compact('users'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email',
            'rol' => 'required|in:administrador,coordinador,analista,instructor',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'rol' => $validated['rol'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->has('is_active'), // Checkbox
        ]);

        registrar_auditoria("Usuario creado", "Se registró el usuario {$validated['email']} con rol {$validated['rol']}");

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }


    public function edit(User $user)
    {
        return view('admin.usuarios.edit', compact('user'));
    }

    // Actualizar usuario
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191',
            'rol' => 'required|string|in:administrador,coordinador,analista,instructor',
            'password' => 'nullable|string|min:8|confirmed', // Solo validar si se cambia
            'is_active' => 'required|boolean', // Validación para el estado activo
        ]);

        // Actualización de los datos
        $user->name = $request->name;
        $user->email = $request->email;
        $user->rol = $request->rol;
        $user->is_active = $request->has('is_active') ? true : false; // Asegura que se marca como activo

        // Si el campo password no está vacío, lo actualizamos
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        // Guardamos los cambios
        $user->save();

        // Registrar acción de auditoría
        registrar_auditoria("Actualización de usuario", "Se actualizó el usuario {$user->name} con el rol {$user->rol}");

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }






    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'rol' => 'required|string|in:administrador,coordinador,analista,instructor',
        ]);

        $user->rol = $request->rol;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Rol actualizado exitosamente.');
    }

    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make('password123'), // Contraseña temporal
        ]);

        return redirect()->route('users.index')->with('success', 'Contraseña reseteada exitosamente.');
    }
}
