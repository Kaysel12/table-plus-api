<?php

namespace App\Exceptions;

use Exception;

class TaskException extends Exception
{
    public static function creationFailed(string $reason): self
    {
        return new self("Error al crear la tarea: $reason");
    }

    public static function updateFailed(string $reason): self
    {
        return new self("Error al actualizar la tarea: $reason");
    }

    public static function notFound(int $id): self
    {
        return new self("Tarea con ID $id no encontrada.");
    }

    public static function deleteFailed(int $id): self
    {
        return new self("No se pudo eliminar la tarea con ID $id.");
    }
    
    public static function imageTooLarge(float $sizeInMB): self
    {
        return new self("La imagen es demasiado grande ({$sizeInMB}MB). El tamaño máximo permitido es 2MB.");
    }

    public static function invalidImageFormat(string $format): self
    {
        return new self("Formato de imagen no válido: {$format}. Formatos permitidos: jpeg, png, jpg, gif.");
    }
}

