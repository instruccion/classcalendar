<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        $coordinaciones = [];

        if ($request->user()->rol === 'administrador') {
            $coordinaciones = DB::table('coordinaciones')->orderBy('nombre')->get();
        }

        return view('profile.edit', [
            'user' => $request->user(),
            'coordinaciones' => $coordinaciones,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Subida de nueva imagen
        if ($request->hasFile('foto_perfil')) {
            $archivo = $request->file('foto_perfil');
            $nombre = 'user_' . $user->id . '.' . $archivo->getClientOriginalExtension();
            $ruta = public_path('assets/images/users');

            if (!file_exists($ruta)) {
                mkdir($ruta, 0755, true);
            }

            $archivo->move($ruta, $nombre);

            $user->foto_perfil = $nombre;
        }

        $user->save(); // <- debe ir al final, para guardar la foto

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
