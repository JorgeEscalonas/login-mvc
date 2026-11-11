<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

interface UserListInterface
{
    /** @return User[] */
    public function all(): array;
}