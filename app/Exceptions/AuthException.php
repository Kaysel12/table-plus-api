<?php

namespace App\Exceptions;

use Exception;

class AuthException extends Exception
{
    public static function invalidCredentials(): self
    {
        return new self('Credenciales inválidas.');
    }

    public static function refreshFailed(): self
    {
        return new self('No se pudo refrescar el token.');
    }
}
