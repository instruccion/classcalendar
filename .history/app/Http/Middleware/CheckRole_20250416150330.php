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
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (Auth::check() && in_array(Auth::user()->rol, $roles)) {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', 'No tienes permiso para acceder a esta sección.');
    }

}
