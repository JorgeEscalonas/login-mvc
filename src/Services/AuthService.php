<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;

class AuthService
{
    public function __construct(private readonly UserRepositoryInterface $users)
    {
    }

    public function attempt(string $email, string $password): ?User
    {
        $user = $this->users->findByEmail(mb_strtolower(trim($email)));

        if ($user === null) {
            // Gasta un tiempo similar al de verificar, para no delatar
            // si el correo existe midiendo lo que tarda la respuesta.
            password_hash($password, PASSWORD_DEFAULT);
            return null;
        }

        if (!password_verify($password, $user->passwordHash)) {
            return null;
        }

        return $user;
    }
}