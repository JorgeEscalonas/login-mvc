<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Role;
use App\Repositories\UserRepositoryInterface;

class RoleMiddleware
{
    public function __construct(
        private readonly Session $session,
        private readonly View $view,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /** @param Role[] $allowedRoles */
    public function allow(array $allowedRoles, callable $handler): callable
    {
        return function () use ($allowedRoles, $handler): void {
            $user = $this->users->findById((int) $this->session->get('user_id'));

            if ($user === null || !in_array($user->role, $allowedRoles, true)) {
                http_response_code(403);
                $this->view->render('errors/403', ['title' => 'Acceso denegado']);
                return;
            }

            $handler();
        };
    }
}