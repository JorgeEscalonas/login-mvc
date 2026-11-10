<?php

declare(strict_types=1);

namespace App\Core;

class AuthMiddleware
{
    public function __construct(private readonly Session $session)
    {
    }

    public function protect(callable $handler): callable
    {
        return function () use ($handler): void {
            if (!$this->session->has('user_id')) {
                header('Location: /login');
                exit;
            }

            $handler();
        };
    }
}