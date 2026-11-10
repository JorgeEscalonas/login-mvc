<?php

declare(strict_types=1);

return [
    'host'    => $_ENV['DB_HOST'],
    'port'    => $_ENV['DB_PORT'],
    'name'    => $_ENV['DB_NAME'],
    'charset' => 'utf8mb4',
    'user'    => $_ENV['DB_USER'],
    'pass'    => $_ENV['DB_PASS'],
];
