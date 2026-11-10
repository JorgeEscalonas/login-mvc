<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserWriterInterface;
use PDOException;

class UserManagementService
{
    public function __construct(
        private readonly UserRepositoryInterface $reader,
        private readonly UserWriterInterface $writer,
    ) {
    }

    /** @return list<User> */
    public function all(): array
    {
        return $this->reader->findAll();
    }

    public function find(int $id): ?User
    {
        return $this->reader->findById($id);
    }

    /** @throws ValidationException */
    public function create(string $name, string $email, string $password, string $confirmation, string $role): void
    {
        $name = trim($name);
        $email = mb_strtolower(trim($email));
        $errors = $this->validate($name, $email, $password, $confirmation, $role, true);

        if ($this->reader->findByEmail($email) !== null) {
            $errors['email'] = 'Ya existe una cuenta con ese correo.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        try {
            $this->writer->create($name, $email, password_hash($password, PASSWORD_DEFAULT), Role::from($role));
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                throw new ValidationException(['email' => 'Ya existe una cuenta con ese correo.']);
            }

            throw $exception;
        }
    }

    /** @throws ValidationException */
    public function update(
        int $id,
        string $name,
        string $email,
        string $password,
        string $confirmation,
        string $role,
        int $actorId,
    ): void {
        $name = trim($name);
        $email = mb_strtolower(trim($email));
        $errors = $this->validate($name, $email, $password, $confirmation, $role, false);
        $existing = $this->reader->findByEmail($email);

        if ($existing !== null && $existing->id !== $id) {
            $errors['email'] = 'Ya existe una cuenta con ese correo.';
        }

        $newRole = Role::tryFrom($role);
        if ($id === $actorId && $newRole !== Role::Admin) {
            $errors['role'] = 'No puedes quitarte tu propio rol de administrador.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $passwordHash = $password === '' ? null : password_hash($password, PASSWORD_DEFAULT);

        try {
            $this->writer->update($id, $name, $email, $newRole, $passwordHash);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                throw new ValidationException(['email' => 'Ya existe una cuenta con ese correo.']);
            }

            throw $exception;
        }
    }

    public function delete(int $id, int $actorId): void
    {
        if ($id === $actorId) {
            throw new ValidationException(['form' => 'No puedes eliminar tu propia cuenta de administrador.']);
        }

        $this->writer->delete($id);
    }

    /** @return array<string, string> */
    private function validate(
        string $name,
        string $email,
        string $password,
        string $confirmation,
        string $role,
        bool $passwordRequired,
    ): array {
        $errors = [];

        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $errors['name'] = 'El nombre debe tener entre 2 y 100 caracteres.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $errors['email'] = 'Ingresa un correo válido.';
        }

        if (Role::tryFrom($role) === null) {
            $errors['role'] = 'Selecciona un rol válido.';
        }

        if ($passwordRequired || $password !== '') {
            if (mb_strlen($password) < 8) {
                $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.';
            } elseif (strlen($password) > 72) {
                $errors['password'] = 'La contraseña no puede superar 72 caracteres.';
            } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
                $errors['password'] = 'La contraseña debe incluir letras y números.';
            }

            if ($password !== $confirmation) {
                $errors['password_confirmation'] = 'Las contraseñas no coinciden.';
            }
        } elseif ($confirmation !== '') {
            $errors['password_confirmation'] = 'Escribe primero la nueva contraseña.';
        }

        return $errors;
    }
}