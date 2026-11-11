<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\ValidationException;
use App\Services\RegisterService;

class RegisterController
{
    public function __construct(
        private readonly RegisterService $register,
        private readonly View $view,
        private readonly Session $session,
        private readonly Csrf $csrf,
    ) {
    }

    public function showForm(): void
    {
        if ($this->session->has('user_id')) {
            header('Location: /dashboard');
            exit;
        }

        $this->renderForm();
    }

    public function store(): void
    {
        $token = $_POST['_csrf'] ?? null;

        if (!$this->csrf->isValid(is_string($token) ? $token : null)) {
            http_response_code(419);
            $this->renderForm(['form' => 'Sesión expirada. Intenta de nuevo.']);
            return;
        }

        $name = $this->input('name');
        $email = $this->input('email');

        try {
            $user = $this->register->register(
                $name,
                $email,
                $this->input('password'),
                $this->input('password_confirmation'),
            );
        } catch (ValidationException $e) {
            http_response_code(422);
            $this->renderForm($e->errors(), ['name' => $name, 'email' => $email]);
            return;
        }

        $this->session->regenerate();
        $this->session->set('user_id', $user->id);
        $this->session->set('user_name', $user->name);
        $this->session->set('user_role', $user->role->value);

        header('Location: /dashboard');
        exit;
    }

    private function input(string $key): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, string> $old
     */
    private function renderForm(array $errors = [], array $old = []): void
    {
        $this->view->render('auth/register', [
            'title'  => 'Crear cuenta',
            'errors' => $errors,
            'old'    => $old,
        ]);
    }
}