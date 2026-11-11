<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;

interface UserWriterInterface
{
    public function create(string $name, string $email, string $passwordHash, Role $role): User;

    public function update(int $id, string $name, string $email, Role $role, ?string $passwordHash): void;

    public function delete(int $id): void;
}