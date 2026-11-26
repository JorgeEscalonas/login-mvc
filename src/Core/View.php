<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class View
{
    public function __construct(
        private readonly string $basePath,
        private readonly Csrf $csrf,
        private readonly Session $session,
    ) {
    }

    public function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="'
            . $this->e($this->csrf->token()) . '">';
    }

    /** @param array<string, mixed> $data */
    public function render(string $view, array $data = []): void
    {
        $content = $this->capture($view, $data);
        $appShell = $view === 'dashboard/index' || str_starts_with($view, 'admin/');

        echo $this->capture('layouts/main', [
            'content' => $content,
            'title'   => $data['title'] ?? 'Login MVC',
            'wide' => $data['wide'] ?? false,
            'pageClass' => (string) ($data['pageClass'] ?? ''),
            'appShell' => $appShell,
            'navName' => (string) $this->session->get('user_name', 'Usuario'),
            'navRole' => (string) $this->session->get('user_role', 'user'),
            'activePath' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
        ]);
    }

    public function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /** @param array<string, mixed> $data */
    private function capture(string $view, array $data): string
    {
        $file = $this->basePath . '/' . $view . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("Vista no encontrada: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }
}