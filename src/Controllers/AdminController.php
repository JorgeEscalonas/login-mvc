<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\User;

class AdminController
{
    public function __construct(
        private readonly User $users,
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