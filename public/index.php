<?php

declare(strict_types=1);

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'");

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
use App\Core\AuthMiddleware;
use App\Core\Csrf;
use App\Controllers\RegisterController;
use App\Controllers\UserManagementController;
use App\Services\RegisterService;
use App\Services\UserManagementService;

Dotenv::createImmutable(__DIR__ . '/..')->load();

if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
}

$config = require __DIR__ . '/../config/database.php';

// --- Infraestructura ---
$session = new Session();
$session->start();

$csrf = new Csrf($session);
$view = new View(__DIR__ . '/../src/Views', $csrf);
$database = new Database($config);

// --- Capas de la aplicación (de abajo hacia arriba) ---
$userRepository = new UserRepository($database);
$authMiddleware = new AuthMiddleware($session, $userRepository);
$authService = new AuthService($userRepository);
$registerService = new RegisterService($userRepository, $userRepository);
$userManagementService = new UserManagementService($userRepository, $userRepository);

$authController = new AuthController($authService, $view, $session, $csrf);
$registerController = new RegisterController($registerService, $view, $session, $csrf);
$userManagementController = new UserManagementController($userManagementService, $view, $session, $csrf);
$dashboardController = new DashboardController($view, $session, $userRepository);

// --- Rutas ---
$router = new Router();

$router->get('/', [$authController, 'showLogin']);
$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->post('/logout', [$authController, 'logout']);

$router->get('/register', [$registerController, 'showForm']);
$router->post('/register', [$registerController, 'store']);

$router->get('/admin/users', $authMiddleware->adminOnly([$userManagementController, 'index']));
$router->get('/admin/users/create', $authMiddleware->adminOnly([$userManagementController, 'create']));
$router->post('/admin/users/store', $authMiddleware->adminOnly([$userManagementController, 'store']));
$router->get('/admin/users/edit', $authMiddleware->adminOnly([$userManagementController, 'edit']));
$router->post('/admin/users/update', $authMiddleware->adminOnly([$userManagementController, 'update']));
$router->post('/admin/users/delete', $authMiddleware->adminOnly([$userManagementController, 'delete']));

$router->get('/dashboard', $authMiddleware->protect([$dashboardController, 'index']));

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');