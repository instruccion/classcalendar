<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        //
    }

    public function render($request, Throwable $exception)
    {
        // 🔒 Interceptamos error 403
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $exception->getStatusCode() === 403) {
            // Verificamos si la URL contiene "/admin/aulas"
            if ($request->is('admin/aulas*')) {
                return redirect()->route('admin.aulas.index')->with('toast', [
                    'type' => 'error',
                    'message' => 'No tienes permiso para eliminar aulas.',
                ]);
            }

            // Caso genérico
            return redirect()->route('dashboard')->with('toast', [
                'type' => 'error',
                'message' => 'Acción no autorizada.',
            ]);
        }

        return parent::render($request, $exception);
    }

}
