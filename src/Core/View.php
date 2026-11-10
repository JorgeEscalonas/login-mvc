<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class View
{
    public function __construct(private readonly string $basePath)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $view, array $data = []): void
    {
        $content = $this->capture($view, $data);

        echo $this->capture('layouts/main', [
            'content' => $content,
            'title'   => $data['title'] ?? 'Login MVC',
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