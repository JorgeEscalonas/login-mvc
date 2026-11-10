<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    private const KEY = 'csrf_token';

    public function __construct(private readonly Session $session)
    {
    }

    public function token(): string
    {
        if (!$this->session->has(self::KEY)) {
            $this->session->set(self::KEY, bin2hex(random_bytes(32)));
        }

        return (string) $this->session->get(self::KEY);
    }

    public function isValid(?string $submitted): bool
    {
        $stored = (string) $this->session->get(self::KEY, '');

        return $stored !== ''
            && $submitted !== null
            && hash_equals($stored, $submitted);
    }
}