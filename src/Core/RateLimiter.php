<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\TooManyAttemptsException;

class RateLimiter
{
    public function __construct(
        private readonly Session $session,
        private readonly int $maxAttempts = 5,
        private readonly int $windowSeconds = 900,
    ) {
    }

    /** @throws TooManyAttemptsException */
    public function ensureNotLocked(string $email, string $ip): void
    {
        $key = $this->key($email, $ip);
        $state = $this->getState($key);

        if (($state['locked_until'] ?? 0) > time()) {
            throw new TooManyAttemptsException((int) ($state['locked_until'] - time()));
        }

        if (($state['started_at'] ?? 0) + $this->windowSeconds <= time()) {
            $this->session->remove($key);
        }
    }

    public function recordFailure(string $email, string $ip): void
    {
        $key = $this->key($email, $ip);
        $state = $this->getState($key);
        $now = time();

        if (($state['started_at'] ?? 0) + $this->windowSeconds <= $now) {
            $state = ['count' => 0, 'started_at' => $now, 'locked_until' => 0];
        }

        $state['count'] = (int) ($state['count'] ?? 0) + 1;

        if ($state['count'] >= $this->maxAttempts) {
            $state['locked_until'] = $now + $this->windowSeconds;
        }

        $this->session->set($key, $state);
    }

    public function reset(string $email, string $ip): void
    {
        $this->session->remove($this->key($email, $ip));
    }

    /** @return array{count?: int, started_at?: int, locked_until?: int} */
    private function getState(string $key): array
    {
        $state = $this->session->get($key, []);

        return is_array($state) ? $state : [];
    }

    private function key(string $email, string $ip): string
    {
        $email = mb_substr(mb_strtolower(trim($email)), 0, 150);

        return 'login_attempt_' . hash('sha256', $email . '|' . $ip);
    }
}