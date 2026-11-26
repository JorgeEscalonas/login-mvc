<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Core\Csrf;
use App\Exceptions\TooManyAttemptsException;
use App\Exceptions\ValidationException;
use App\Core\RateLimiter;

class AuthController
{
    public function __construct(
        private readonly User $users,
        private readonly View $view,
        private readonly Session $session,
        private readonly Csrf $csrf,
        private readonly RateLimiter $limiter,
    ) {
    }

    public function showLogin(): void
    {
        if ($this->session->has('user_id')) {
            $this->redirect('/dashboard');
        }

        $notice = ($_GET['password_reset'] ?? '') === '1'
            ? 'Contraseña actualizada. Ya puedes iniciar sesión.'
            : null;
        $this->renderLogin(notice: $notice);
    }

    public function login(): void
    {
        if (!$this->csrfIsValid()) {
            http_response_code(419);
            $this->renderLogin('Sesión expirada. Intenta de nuevo.');
            return;
        }

        $email = (string) ($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';

        try {
            $this->limiter->ensureNotLocked($email, $ip);
        } catch (TooManyAttemptsException $e) {
            $minutes = (int) ceil($e->retryAfterSeconds() / 60);
            http_response_code(429);
            header('Retry-After: ' . $e->retryAfterSeconds());
            $this->renderLogin("Demasiados intentos. Inténtalo de nuevo en {$minutes} minutos.", $email);
            return;
        }

        try {
            $user = $this->users->attempt($email, $password);
        } catch (ValidationException $exception) {
            http_response_code(422);
            $errors = $exception->errors();
            $this->renderLogin(reset($errors) ?: 'Completa los datos correctamente.', $email);
            return;
        }

        if ($user === null) {
            $this->limiter->recordFailure($email, $ip);
            http_response_code(401);
            $this->renderLogin('Credenciales incorrectas.', $email);
            return;
        }

        $this->limiter->reset($email, $ip);

        $this->session->regenerate();
        $this->session->set('user_id', $user->id);
        $this->session->set('user_name', $user->name);
        $this->session->set('user_role', $user->role->value);

        $this->redirect('/dashboard');
    }
    
    private function renderLogin(?string $error = null, string $email = '', ?string $notice = null): void
    {
        $this->view->render('auth/login', [
            'title' => 'Iniciar sesión',
            'error' => $error,
            'email' => $email,
            'notice' => $notice,
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