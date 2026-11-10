<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Repositories\UserRepositoryInterface;

class DashboardController
{
    public function __construct(
        private readonly View $view,
        private readonly Session $session,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function index(): void
    {
        $user = $this->users->findById((int) $this->session->get('user_id'));

        if ($user === null) {
            $this->session->destroy();
            header('Location: /login');
            exit;
        }

        $this->session->set('user_name', $user->name);
        $this->session->set('user_role', $user->role->value);

        $this->view->render('dashboard/index', [
            'title' => 'Panel',
            'name'  => $user->name,
            'role'  => $user->role->value,
        ]);
    }
}