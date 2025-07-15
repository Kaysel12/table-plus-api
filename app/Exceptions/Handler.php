<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof TaskException) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 400);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ruta no encontrada',
            ], 404);
        }

        return parent::render($request, $exception);
    }

    public function shouldReturnJson($request, Throwable $e): bool
    {
        return true;
    }

    protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
    {
        return response()->json(['message' => 'Token no válido o no proporcionado.'], 401);
    }
}
