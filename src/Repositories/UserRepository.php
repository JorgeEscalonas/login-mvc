<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findBYEmail(string $email): ?User
    {
       $statement = $this->database->getConnection()->prepare(
            'SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1'
        );

        $statement->execute(['email' => $email]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return new User(
            id: (int)$row['id'],
            name: $row['name'],
            email: $row['email'],
            passwordHash: $row['password']
        );
    }
}