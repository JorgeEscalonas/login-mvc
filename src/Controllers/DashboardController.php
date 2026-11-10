<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;

class DashboardController
{
    public function __construct(
        private readonly View $view,
        private readonly Session $session,
    ) {
    }

    public function index(): void
    {
        if (!$this->session->has('user_id')) {
            header('Location: /login');
            exit;
        }

        $this->view->render('dashboard/index', [
            'title' => 'Panel',
            'name'  => (string) $this->session->get('user_name'),
        ]);
    }
}