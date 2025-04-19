<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade; // Asegúrate que Blade esté importado

// Importa la clase del componente que quieres registrar
use App\View\Components\AppLayout;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Sin cambios aquí
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // --- REGISTRO EXPLÍCITO DEL COMPONENTE app-layout ---
        // Esto le dice a Blade que use la clase AppLayout cuando encuentre <x-app-layout>
        Blade::component('app-layout', AppLayout::class);
        // -----------------------------------------------------

        // View Composer existente (sin cambios)
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();

                // Lógica para obtener avatar (sin cambios)
                $avatar = $user->foto && file_exists(public_path('uploads/' . $user->foto))
                    ? asset('uploads/' . $user->foto)
                    : asset('assets/images/users/avatar-default.png');

                // Lógica para obtener nombre de rol (sin cambios, asumiendo rol_id)
                $roles = [1 => 'Administrador', 2 => 'Coordinador', 3 => 'Analista', 4 => 'Instructor'];
                $nombreRol = $roles[$user->rol_id] ?? 'Usuario'; // O usa $user->rol si es string

                // Pasar variables a todas las vistas (sin cambios)
                $view->with([
                    'avatar' => $avatar,
                    'nombreRol' => $nombreRol,
                ]);
            }
        });

        // La función toast() ya fue eliminada de aquí (correcto)

    }
}
