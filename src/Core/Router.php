<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<string, array<string, callable|array>> */
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = $path === '/' ? '/' : rtrim($path, '/');

        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo '404 - Página no encontrada';
            return;
        }

        if (is_array($handler)) {
            [$class, $action] = $handler;
            (new $class())->$action();
            return;
        }

        $handler();
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }
}