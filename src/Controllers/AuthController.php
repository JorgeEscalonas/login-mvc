<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use App\Core\Csrf;

class AuthController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly View $view,
        private readonly Session $session,
        private readonly Csrf $csrf,
    ) {
    }

    public function showLogin(): void
    {
        if ($this->session->has('user_id')) {
            $this->redirect('/dashboard');
        }

        $this->renderLogin();
    }

    public function login(): void
    {
        if (!$this->csrf->isValid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->renderLogin('Sesión expirada. Intenta de nuevo.');
            return;
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            http_response_code(422);
            $this->renderLogin('Completa todos los campos.', $email);
            return;
        }

        $user = $this->auth->attempt($email, $password);

        if ($user === null) {
            http_response_code(401);
            $this->renderLogin('Credenciales incorrectas.', $email);
            return;
        }

        $this->session->regenerate();
        $this->session->set('user_id', $user->id);
        $this->session->set('user_name', $user->name);

        $this->redirect('/dashboard');
    }

    private function renderLogin(?string $error = null, string $email = ''): void
    {
        $this->view->render('auth/login', [
            'title' => 'Iniciar sesión',
            'error' => $error,
            'email' => $email,
        ]);
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

        public function logout(): void
    {
        if (!$this->csrf->isValid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Solicitud no válida.';
            return;
        }

        $this->session->destroy();
        $this->redirect('/login');
    }

    private function csrfIsValid(): bool
    {
        $token = $_POST['_csrf'] ?? null;

        return $this->csrf->isValid(is_string($token) ? $token : null);
    }
}