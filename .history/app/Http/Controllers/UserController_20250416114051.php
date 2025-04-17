<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Coordinacion;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador']);
    }

    public function index()
    {
        $users = User::all();
        $coordinaciones = Coordinacion::orderBy('nombre')->get();

        return view('admin.usuarios.index', compact('users', 'coordinaciones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email',
            'rol' => 'required|in:administrador,coordinador,analista,instructor',
            'coordinacion_id' => 'nullable|exists:coordinaciones,id',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'rol' => $validated['rol'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->has('is_active'),
            'coordinacion_id' => $validated['coordinacion_id'] ?? null, // GUARDADO
        ]);

        registrar_auditoria("Usuario creado", "Se registró el usuario {$validated['email']} con rol {$validated['rol']}");

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }



    public function edit(User $user)
    {
        return view('admin.usuarios.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191',
            'rol' => 'required|string|in:administrador,coordinador,analista,instructor',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'required|boolean',
            'coordinacion_id' => 'nullable|exists:coordinaciones,id', // NUEVO
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->rol = $request->rol;
        $user->is_active = $request->has('is_active');
        $user->coordinacion_id = $request->coordinacion_id ?? null; // ASIGNACIÓN

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        registrar_auditoria("Actualización de usuario", "Se actualizó el usuario {$user->name} con el rol {$user->rol}");

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }



    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'rol' => 'required|string|in:administrador,coordinador,analista,instructor',
        ]);

        $user->rol = $request->rol;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Rol actualizado exitosamente.');
    }

    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make('password123'),
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
