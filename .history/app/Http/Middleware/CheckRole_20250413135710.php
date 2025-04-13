<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (Auth::check() && Auth::user()->rol === $role) {
            /*return $next($request);*/
            
        }

        abort(403, 'No tienes permiso para acceder a esta sección.');
    }
}
