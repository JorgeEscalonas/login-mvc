<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Exceptions\ValidationException;
use PDOException;

class User
{
    public function __construct(
        private readonly Database $database,
        public readonly int $id = 0,
        public readonly string $name = '',
        public readonly string $email = '',
        public readonly string $passwordHash = '',
        public readonly Role $role = Role::User,
    ) {
    }

    public function findByEmail(string $email): ?User
    {
        $statement = $this->database->getConnection()->prepare(
            'SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => mb_strtolower(trim($email))]);
        $row = $statement->fetch();

        return $row === false ? null : $this->fromRow($row);
    }

    public function findById(int $id): ?User
    {
        $statement = $this->database->getConnection()->prepare(
            'SELECT id, name, email, password, role FROM users WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->fromRow($row);
    }

    /** @return list<User> */
    public function all(): array
    {
        $statement = $this->database->getConnection()->query(
            'SELECT id, name, email, password, role FROM users ORDER BY id'
        );
        $users = [];

        foreach ($statement->fetchAll() as $row) {
            $users[] = $this->fromRow($row);
        }

        return $users;
    }

    /** @throws ValidationException */
    public function attempt(string $email, string $password): ?User
    {
        $email = mb_strtolower(trim($email));
        $errors = [];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Ingresa un correo válido.';
        }
        if ($password === '') {
            $errors['password'] = 'Ingresa tu contraseña.';
        }
        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $user = $this->findByEmail($email);

        if ($user === null) {
            password_hash($password, PASSWORD_DEFAULT);
            return null;
        }

        return password_verify($password, $user->passwordHash) ? $user : null;
    }

    public function createPasswordResetToken(string $email): ?string
    {
        $email = mb_strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            return null;
        }

        if ($this->findByEmail($email) === null) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $statement = $this->database->getConnection()->prepare(
            'INSERT INTO password_reset_tokens (email, token_hash, expires_at)
             VALUES (:email, :token_hash, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE))
             ON DUPLICATE KEY UPDATE
                token_hash = :token_hash_update,
                expires_at = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)'
        );
        $tokenHash = hash('sha256', $token);
        $statement->execute([
            'email' => $email,
            'token_hash' => $tokenHash,
            'token_hash_update' => $tokenHash,
        ]);

        return $token;
    }

    public function isPasswordResetTokenValid(string $email, string $token): bool
    {
        $email = mb_strtolower(trim($email));
        $token = $this->normalizeResetToken($token);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $token === null) {
            return false;
        }

        $statement = $this->database->getConnection()->prepare(
            'SELECT token_hash FROM password_reset_tokens
             WHERE email = :email AND expires_at > CURRENT_TIMESTAMP LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $reset = $statement->fetch();

        return $reset !== false && hash_equals((string) $reset['token_hash'], hash('sha256', $token));
    }

    /** @throws ValidationException */
    public function resetPassword(
        string $email,
        string $token,
        string $password,
        string $confirmation,
    ): void {
        $email = mb_strtolower(trim($email));
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $errors['email'] = 'Ingresa un correo válido.';
        }
        $errors = array_merge($errors, $this->validatePassword($password, $confirmation, true));

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $token = $this->normalizeResetToken($token);
        if ($token === null) {
            throw new ValidationException(['form' => 'El enlace no es válido o ya venció. Solicita uno nuevo.']);
        }

        $pdo = $this->database->getConnection();
        $pdo->beginTransaction();

        try {
            $statement = $pdo->prepare(
                'SELECT token_hash FROM password_reset_tokens
                 WHERE email = :email AND expires_at > CURRENT_TIMESTAMP FOR UPDATE'
            );
            $statement->execute(['email' => $email]);
            $reset = $statement->fetch();

            if ($reset === false || !hash_equals((string) $reset['token_hash'], hash('sha256', $token))) {
                $pdo->rollBack();
                throw new ValidationException(['form' => 'El enlace no es válido o ya venció. Solicita uno nuevo.']);
            }

            $statement = $pdo->prepare('UPDATE users SET password = :password WHERE email = :email');
            $statement->execute([
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'email' => $email,
            ]);

            if ($statement->rowCount() !== 1) {
                $pdo->rollBack();
                throw new ValidationException(['form' => 'No se pudo actualizar la contraseña.']);
            }

            $statement = $pdo->prepare('DELETE FROM password_reset_tokens WHERE email = :email');
            $statement->execute(['email' => $email]);
            $pdo->commit();
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    /** @throws ValidationException */
    public function register(string $name, string $email, string $password, string $confirmation): User
    {
        $name = trim($name);
        $email = mb_strtolower(trim($email));
        $errors = $this->validateIdentity($name, $email);
        $errors = array_merge($errors, $this->validatePassword($password, $confirmation, true));

        if ($this->findByEmail($email) !== null) {
            $errors['email'] = 'No se pudo completar el registro con ese correo.';
        }
        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $this->create($name, $email, password_hash($password, PASSWORD_DEFAULT), Role::User);
    }

    /** @throws ValidationException */
    public function createManaged(
        string $name,
        string $email,
        string $password,
        string $confirmation,
        string $role,
    ): User {
        $name = trim($name);
        $email = mb_strtolower(trim($email));
        $errors = $this->validateIdentity($name, $email);
        $errors = array_merge($errors, $this->validatePassword($password, $confirmation, true));

        $newRole = Role::tryFrom($role);
        if ($newRole === null) {
            $errors['role'] = 'Selecciona un rol válido.';
        }
        if ($this->findByEmail($email) !== null) {
            $errors['email'] = 'Ya existe una cuenta con ese correo.';
        }
        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $this->create($name, $email, password_hash($password, PASSWORD_DEFAULT), $newRole);
    }

    /** @throws ValidationException */
    public function updateManaged(
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
        $errors = $this->validateIdentity($name, $email);
        $errors = array_merge($errors, $this->validatePassword($password, $confirmation, false));

        $newRole = Role::tryFrom($role);
        if ($newRole === null) {
            $errors['role'] = 'Selecciona un rol válido.';
        }

        $existing = $this->findByEmail($email);
        if ($existing !== null && $existing->id !== $id) {
            $errors['email'] = 'Ya existe una cuenta con ese correo.';
        }
        if ($id === $actorId && $newRole !== Role::Admin) {
            $errors['role'] = 'No puedes quitarte tu propio rol de administrador.';
        }
        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $passwordHash = $password === '' ? null : password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo = $this->database->getConnection();
            $query = 'UPDATE users SET name = :name, email = :email, role = :role';
            $parameters = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'role' => $newRole->value,
            ];
            if ($passwordHash !== null) {
                $query .= ', password = :password';
                $parameters['password'] = $passwordHash;
            }
            $query .= ' WHERE id = :id';
            $pdo->prepare($query)->execute($parameters);
        } catch (PDOException $exception) {
            $this->handleWriteException($exception);
        }
    }

    /** @throws ValidationException */
    public function deleteManaged(int $id, int $actorId): void
    {
        if ($id <= 0) {
            throw new ValidationException(['form' => 'El usuario seleccionado no es válido.']);
        }
        if ($id === $actorId) {
            throw new ValidationException(['form' => 'No puedes eliminar tu propia cuenta de administrador.']);
        }

        $statement = $this->database->getConnection()->prepare('DELETE FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    private function create(string $name, string $email, string $passwordHash, Role $role): User
    {
        try {
            $pdo = $this->database->getConnection();
            $statement = $pdo->prepare(
                'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)'
            );
            $statement->execute([
                'name' => $name,
                'email' => $email,
                'password' => $passwordHash,
                'role' => $role->value,
            ]);

            return new User($this->database, (int) $pdo->lastInsertId(), $name, $email, $passwordHash, $role);
        } catch (PDOException $exception) {
            $this->handleWriteException($exception);
        }
    }

    /** @return array<string, string> */
    private function validateIdentity(string $name, string $email): array
    {
        $errors = [];

        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $errors['name'] = 'El nombre debe tener entre 2 y 100 caracteres.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $errors['email'] = 'Ingresa un correo válido.';
        }

        return $errors;
    }

    private function normalizeResetToken(string $token): ?string
    {
        $token = trim($token);
        if (preg_match('/Password reset token:\s*([a-f0-9]{64})/i', $token, $matches) === 1) {
            $token = $matches[1];
        }

        return preg_match('/\A[a-f0-9]{64}\z/i', $token) === 1 ? $token : null;
    }

    /** @return array<string, string> */
    private function validatePassword(string $password, string $confirmation, bool $required): array
    {
        $errors = [];

        if ($required || $password !== '') {
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

    /** @param array<string, mixed> $row */
    private function fromRow(array $row): User
    {
        return new User(
            $this->database,
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['email'],
            (string) $row['password'],
            Role::from((string) $row['role']),
        );
    }

    private function handleWriteException(PDOException $exception): never
    {
        if ($exception->getCode() === '23000') {
            throw new ValidationException(['email' => 'Ya existe una cuenta con ese correo.']);
        }

        throw $exception;
    }
}