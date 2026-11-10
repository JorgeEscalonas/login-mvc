<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__ . '/..')->load();

$router = new Router();

$router->get('/', function () {
    echo 'Pagina de inicio';
});

$router->get('/pruebe', function () {
    echo 'Ruta de prueba funcionando';
});

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);