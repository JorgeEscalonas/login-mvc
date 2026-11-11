<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class LoginAttemptRepository implements LoginAttemptRepositoryInterface
{
    public function __construct(private readonly Database $database)
    {
    }

    public function record(string $email, string $ip): void
    {
        $statement = $this->database->getConnection()->prepare(
            'INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip)'
        );
        $statement->execute(['email' => $email, 'ip' => $ip]);
    }

    public function countRecent(string $email, string $ip, int $windowSeconds): int
    {
        $statement = $this->database->getConnection()->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE email = :email AND ip_address = :ip
               AND attempted_at > (NOW() - INTERVAL :seconds SECOND)'
        );
        $statement->bindValue('email', $email);
        $statement->bindValue('ip', $ip);
        $statement->bindValue('seconds', $windowSeconds, \PDO::PARAM_INT);
        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    public function clear(string $email, string $ip): void
    {
        $statement = $this->database->getConnection()->prepare(
            'DELETE FROM login_attempts WHERE email = :email AND ip_address = :ip'
        );
        $statement->execute(['email' => $email, 'ip' => $ip]);
    }
}