<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__ . '/..')->load();
$config = require __DIR__ . '/../config/database.php';

$pdo = (new Database($config))->getConnection();

$statement = $pdo->prepare(
    'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)'
);

$statement->execute([
    'name'     => 'Administrador',
    'email'    => 'admin@example.com',
    'password' => password_hash('Admin1234', PASSWORD_DEFAULT),
]);

echo "Usuario de prueba creado: admin@example.com / Admin1234" . PHP_EOL;