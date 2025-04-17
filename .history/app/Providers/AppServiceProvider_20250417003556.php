<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $avatar = $user->foto && file_exists(public_path('uploads/' . $user->foto))
                    ? asset('uploads/' . $user->foto)
                    : asset('assets/images/users/avatar-default.png');

                $roles = [1 => 'Administrador', 2 => 'Coordinador', 3 => 'Analista', 4 => 'Instructor'];
                $nombreRol = $roles[$user->rol_id] ?? 'Usuario';

                $view->with([
                    'avatar' => $avatar,
                    'nombreRol' => $nombreRol,
                ]);
            }
        });
    }
}
