<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TooManyAttemptsException;
use App\Repositories\LoginAttemptRepositoryInterface;

class RateLimiter
{
    public function __construct(
        private readonly LoginAttemptRepositoryInterface $attempts,
        private readonly int $maxAttempts = 5,
        private readonly int $windowSeconds = 900,
    ) {
    }

    /** @throws TooManyAttemptsException */
    public function ensureNotLocked(string $email, string $ip): void
    {
        $count = $this->attempts->countRecent($this->key($email), $ip, $this->windowSeconds);

        if ($count >= $this->maxAttempts) {
            throw new TooManyAttemptsException($this->windowSeconds);
        }
    }

    public function recordFailure(string $email, string $ip): void
    {
        $this->attempts->record($this->key($email), $ip);
    }

    public function reset(string $email, string $ip): void
    {
        $this->attempts->clear($this->key($email), $ip);
    }

    private function key(string $email): string
    {
        return mb_substr(mb_strtolower(trim($email)), 0, 150);
    }
}