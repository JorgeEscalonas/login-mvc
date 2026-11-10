<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\ValidationException;
use App\Models\User;
use App\Services\UserManagementService;

class UserManagementController
{
    public function __construct(
        private readonly UserManagementService $users,
        private readonly View $view,
        private readonly Session $session,
        private readonly Csrf $csrf,
    ) {
    }

    public function index(): void
    {
        $this->view->render('admin/users/index', [
            'title' => 'Administrar usuarios',
            'users' => $this->users->all(),
            'currentUserId' => (int) $this->session->get('user_id'),
            'pageClass' => 'wide-card',
        ]);
    }

    public function create(): void
    {
        $this->renderForm();
    }

    public function store(): void
    {
        if (!$this->csrfIsValid()) {
            http_response_code(419);
            $this->renderForm(['form' => 'Sesión expirada. Intenta de nuevo.'], $this->oldInput());
            return;
        }

        $old = $this->oldInput();

        try {
            $this->users->create(
                $old['name'],
                $old['email'],
                $this->input('password'),
                $this->input('password_confirmation'),
                $old['role'],
            );
        } catch (ValidationException $exception) {
            http_response_code(422);
            $this->renderForm($exception->errors(), $old);
            return;
        }

        $this->redirect('/admin/users');
    }

    public function edit(): void
    {
        $user = $this->users->find($this->requestId());

        if ($user === null) {
            http_response_code(404);
            echo '404 - Usuario no encontrado';
            return;
        }

        $this->renderForm([], [], $user);
    }

    public function update(): void
    {
        if (!$this->csrfIsValid()) {
            http_response_code(419);
            echo 'Solicitud no válida.';
            return;
        }

        $id = $this->requestId();
        $user = $this->users->find($id);

        if ($user === null) {
            http_response_code(404);
            echo '404 - Usuario no encontrado';
            return;
        }

        $old = $this->oldInput();

        try {
            $this->users->update(
                $id,
                $old['name'],
                $old['email'],
                $this->input('password'),
                $this->input('password_confirmation'),
                $old['role'],
                (int) $this->session->get('user_id'),
            );
        } catch (ValidationException $exception) {
            http_response_code(422);
            $this->renderForm($exception->errors(), $old, $user);
            return;
        }

        $this->redirect('/admin/users');
    }

    public function delete(): void
    {
        if (!$this->csrfIsValid()) {
            http_response_code(419);
            echo 'Solicitud no válida.';
            return;
        }

        try {
            $this->users->delete(
                $this->requestId(),
                (int) $this->session->get('user_id'),
            );
        } catch (ValidationException $exception) {
            http_response_code(422);
            $this->view->render('admin/users/index', [
                'title' => 'Administrar usuarios',
                'users' => $this->users->all(),
                'currentUserId' => (int) $this->session->get('user_id'),
                'error' => $exception->errors()['form'],
                'pageClass' => 'wide-card',
            ]);
            return;
        }

        $this->redirect('/admin/users');
    }

    private function renderForm(array $errors = [], array $old = [], ?User $user = null): void
    {
        $this->view->render('admin/users/form', [
            'title' => $user === null ? 'Crear usuario' : 'Editar usuario',
            'errors' => $errors,
            'old' => $old,
            'user' => $user,
            'action' => $user === null ? '/admin/users/store' : '/admin/users/update?id=' . $user->id,
            'pageClass' => 'form-card',
        ]);
    }

    /** @return array{name: string, email: string, role: string} */
    private function oldInput(): array
    {
        return [
            'name' => $this->input('name'),
            'email' => mb_strtolower(trim($this->input('email'))),
            'role' => $this->input('role'),
        ];
    }

    private function input(string $key): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    private function requestId(): int
    {
        $id = filter_var($_POST['id'] ?? $_GET['id'] ?? null, FILTER_VALIDATE_INT);

        return is_int($id) && $id > 0 ? $id : 0;
    }

    private function csrfIsValid(): bool
    {
        $token = $_POST['_csrf'] ?? null;

        return $this->csrf->isValid(is_string($token) ? $token : null);
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }
}