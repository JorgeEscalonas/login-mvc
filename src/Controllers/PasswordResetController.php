<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\View;
use App\Exceptions\ValidationException;
use App\Models\User;

class PasswordResetController
{
    public function __construct(
        private readonly User $users,
        private readonly View $view,
        private readonly Csrf $csrf,
    ) {
    }

    public function showRequestForm(): void
    {
        $this->renderRequestForm();
    }

    public function requestReset(): void
    {
        header('Cache-Control: no-store, private');

        if (!$this->csrfIsValid()) {
            http_response_code(419);
            $this->renderRequestForm(notice: 'La sesión expiró. Inténtalo de nuevo.');
            return;
        }

        $email = $this->input('email');
        $token = $this->isLocalDevelopment()
            ? $this->users->createPasswordResetToken($email)
            : null;

        if ($token !== null) {
            error_log('Password reset token: ' . $token . ' (usa la solicitud más reciente)');
        }

        $this->renderRequestForm(
            email: $email,
            stage: 'token',
            notice: 'Si el correo está registrado, copia el token más reciente desde la terminal del servidor. Puedes pegar el token o la línea completa. Vence en 15 minutos.',
        );
    }

    public function verifyToken(): void
    {
        header('Cache-Control: no-store, private');

        $email = $this->input('email');
        $token = $this->input('token');

        if (!$this->csrfIsValid()) {
            http_response_code(419);
            $this->renderRequestForm(
                email: $email,
                token: $token,
                stage: 'token',
                errors: ['form' => 'La sesión expiró. Inténtalo de nuevo.'],
            );
            return;
        }

        if (!$this->users->isPasswordResetTokenValid($email, $token)) {
            http_response_code(422);
            $this->renderRequestForm(
                email: $email,
                token: $token,
                stage: 'token',
                errors: ['form' => 'El token no es válido o ya venció. Solicita uno nuevo.'],
            );
            return;
        }

        $this->renderResetForm($email, $token);
    }

    public function resetPassword(): void
    {
        header('Cache-Control: no-store, private');

        if (!$this->csrfIsValid()) {
            http_response_code(419);
            $this->renderResetForm(
                email: $this->input('email'),
                token: $this->input('token'),
                errors: ['form' => 'La sesión expiró. Inténtalo de nuevo.'],
            );
            return;
        }

        $email = $this->input('email');
        $token = $this->input('token');

        try {
            $this->users->resetPassword(
                $email,
                $token,
                $this->input('password'),
                $this->input('password_confirmation'),
            );
        } catch (ValidationException $exception) {
            http_response_code(422);
            $this->renderResetForm($email, $token, errors: $exception->errors());
            return;
        }

        header('Location: /login?password_reset=1');
        exit;
    }

    /** @param array<string, string> $errors */
    private function renderRequestForm(
        string $email = '',
        string $token = '',
        string $stage = 'email',
        ?string $notice = null,
        array $errors = [],
    ): void
    {
        $this->view->render('auth/forgot-password', [
            'title' => 'Recuperar contraseña',
            'email' => $email,
            'token' => $token,
            'stage' => $stage,
            'notice' => $notice,
            'errors' => $errors,
        ]);
    }

    /** @param array<string, string> $errors */
    private function renderResetForm(
        string $email = '',
        string $token = '',
        ?string $notice = null,
        array $errors = [],
    ): void {
        $this->view->render('auth/reset-password', [
            'title' => 'Crear nueva contraseña',
            'email' => $email,
            'token' => $token,
            'notice' => $notice,
            'errors' => $errors,
        ]);
    }

    private function input(string $key): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    private function csrfIsValid(): bool
    {
        $token = $_POST['_csrf'] ?? null;

        return $this->csrf->isValid(is_string($token) ? $token : null);
    }

    private function isLocalDevelopment(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'development') === 'development'
            && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
    }
}