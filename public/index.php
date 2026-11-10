<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Core\Database;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__ . '/..')->load();
$config = require __DIR__ . '/../config/database.php';

// --- Infraestructura ---
$session = new Session();
$session->start();

$view = new View(__DIR__ . '/../src/Views');
$database = new Database($config);

// --- Capas de la aplicación (de abajo hacia arriba) ---
$userRepository = new UserRepository($database);
$authService = new AuthService($userRepository);

$authController = new AuthController($authService, $view, $session);
$dashboardController = new DashboardController($view, $session);

// --- Rutas ---
$router = new Router();

$router->get('/', [$authController, 'showLogin']);
$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->get('/dashboard', [$dashboardController, 'index']);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);