<?php

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;

if (!function_exists('registrar_auditoria')) {
    function registrar_auditoria(string $accion, ?string $descripcion = null): void
    {
        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => $accion,
            'descripcion' => $descripcion,
            'ip' => request()->ip(),
        ]);
    }
}
