<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use App\Models\Role;

class UserRepository implements UserRepositoryInterface, UserWriterInterface, UserListInterface
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findBYEmail(string $email): ?User
    {
       $statement = $this->database->getConnection()->prepare(
            'SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1'
        );

        $statement->execute(['email' => $email]);

        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }
        return new User(
            id: (int) $row['id'],
            name: $row['name'],
            email: $row['email'],
            passwordHash: $row['password'],
            role: Role::from($row['role']),
        );
    }

    public function findById(int $id): ?User
    {
        $statement = $this->database->getConnection()->prepare(
            'SELECT id, name, email, password, role FROM users WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->toUser($row);
    }

    /** @return User[] */
    public function all(): array
    {
        $statement = $this->database->getConnection()->query(
            'SELECT id, name, email, password, role FROM users ORDER BY id'
        );

        $users = [];

        foreach ($statement->fetchAll() as $row) {
            $users[] = $this->toUser($row);
        }

        return $users;
    }

        public function create(string $name, string $email, string $passwordHash, Role $role): User
    {
        $pdo = $this->database->getConnection();

        $statement = $pdo->prepare(
            'INSERT INTO users (name, email, password, role)
             VALUES (:name, :email, :password, :role)'
        );
        $statement->execute([
            'name'     => $name,
            'email'    => $email,
            'password' => $passwordHash,
            'role'     => $role->value,
        ]);

        return new User(
            id: (int) $pdo->lastInsertId(),
            name: $name,
            email: $email,
            passwordHash: $passwordHash,
            role: $role,
        );
    }

    public function update(int $id, string $name, string $email, Role $role, ?string $passwordHash): void
    {
        $pdo = $this->database->getConnection();
        $query = 'UPDATE users SET name = :name, email = :email, role = :role';
        $parameters = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'role' => $role->value,
        ];

        if ($passwordHash !== null) {
            $query .= ', password = :password';
            $parameters['password'] = $passwordHash;
        }

        $query .= ' WHERE id = :id';
        $statement = $pdo->prepare($query);
        $statement->execute($parameters);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->getConnection()->prepare('DELETE FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    /** @param array<string, mixed> $row */
    private function toUser(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            name: (string) $row['name'],
            email: (string) $row['email'],
            passwordHash: (string) $row['password'],
            role: Role::from((string) $row['role']),
        );
    }
}