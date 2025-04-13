<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador']);
    }

    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'rol' => 'required|string|in:administrador,analista,coordinador,instructor',
        ]);

        $user->rol = $request->rol;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Rol actualizado correctamente.');
    }
}
