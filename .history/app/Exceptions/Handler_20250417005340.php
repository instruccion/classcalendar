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
        // Redirección personalizada para errores 403
        if ($exception instanceof HttpException && $exception->getStatusCode() === 403) {
            return redirect()->back()->with('toast', [
                'type' => 'error',
                'message' => 'No tienes permiso para realizar esta acción.',
            ]);
        }

        return parent::render($request, $exception);
    }
}
