<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'code'    => $code,
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public static function error(string $message = 'Error', int $code = 500, mixed $data = null): JsonResponse
    {
        return response()->json([
            'code'    => $code,
            'status'  => 'error',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public static function validationError(array $errors, string $message = 'Datos inválidos', int $code = 422): JsonResponse
    {
        return response()->json([
            'code'    => $code,
            'status'  => 'fail',
            'message' => $message,
            'data'    => $errors,
        ], $code);
    }
}
