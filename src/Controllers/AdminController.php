<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Repositories\UserListInterface;

class AdminController
{
    public function __construct(
        private readonly UserListInterface $users,
        private readonly View $view,
    ) {
    }

    public function index(): void
    {
        $this->view->render('admin/index', [
            'title' => 'Administración',
            'users' => $this->users->all(),
            'wide' => true,
        ]);
    }
}