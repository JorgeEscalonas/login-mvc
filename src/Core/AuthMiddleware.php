<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Role;
use App\Repositories\UserRepositoryInterface;

class AuthMiddleware
{
    public function __construct(
        private readonly Session $session,
        private readonly UserRepositoryInterface $users,
    ) {
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

    public function adminOnly(callable $handler): callable
    {
        return function () use ($handler): void {
            if (!$this->session->has('user_id')) {
                header('Location: /login');
                exit;
            }

            $user = $this->users->findById((int) $this->session->get('user_id'));

            if ($user === null || $user->role !== Role::Admin) {
                http_response_code(403);
                echo '403 - Acceso denegado';
                return;
            }

            $handler();
        };
    }
}