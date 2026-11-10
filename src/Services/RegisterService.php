<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserWriterInterface;
use PDOException;

class RegisterService
{
    public function __construct(
        private readonly UserRepositoryInterface $reader,
        private readonly UserWriterInterface $writer,
    ) {
    }

    /** @throws ValidationException */
    public function register(string $name, string $email, string $password, string $confirmation): User
    {
        $name = trim($name);
        $email = mb_strtolower(trim($email));

        $errors = $this->validate($name, $email, $password, $confirmation);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        try {
            return $this->writer->create(
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                Role::User
            );
        } catch (PDOException $e) {
            // Código 23000: violación de UNIQUE (correo ya registrado)
            if ($e->getCode() === '23000') {
                throw new ValidationException(['email' => 'No se pudo completar el registro con ese correo.']);
            }
            throw $e;
        }
    }

    /** @return array<string, string> */
    private function validate(string $name, string $email, string $password, string $confirmation): array
    {
        $errors = [];

        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $errors['name'] = 'El nombre debe tener entre 2 y 100 caracteres.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $errors['email'] = 'Ingresa un correo válido.';
        } elseif ($this->reader->findByEmail($email) !== null) {
            $errors['email'] = 'No se pudo completar el registro con ese correo.';
        }

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

        return $errors;
    }
}