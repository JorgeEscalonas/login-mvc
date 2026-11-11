<?php

declare(strict_types=1);

namespace App\Repositories;

interface LoginAttemptRepositoryInterface
{
    public function record(string $email, string $ip): void;

    public function countRecent(string $email, string $ip, int $windowSeconds): int;

    public function clear(string $email, string $ip): void;
}