<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class TooManyAttemptsException extends RuntimeException
{
    public function __construct(private readonly int $retryAfterSeconds)
    {
        parent::__construct('Demasiados intentos.');
    }

    public function retryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }
}