<?php

namespace App\Actions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class HandleError
{
    public function __invoke(Throwable $e): Response
    {
        // Interceptar error 403 (Forbidden)
        if ($e instanceof HttpException && $e->getStatusCode() === 403) {
            // Personalizar mensaje y redirección
            return redirect()->route('admin.aulas.index')->with('toast', [
                'type' => 'error',
                'message' => 'No tienes permiso para eliminar aulas.'
            ]);
        }

        // Devolver el error como está si no es un 403
        report($e);
        throw $e;
    }
}
