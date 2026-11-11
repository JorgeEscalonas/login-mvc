<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class ValidationException extends RuntimeException
{
    /** @param array<string, string> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('Datos de registro no válidos.');
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }
}