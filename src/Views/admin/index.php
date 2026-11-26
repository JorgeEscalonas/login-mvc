<?php
$users = $users ?? [];
$totalUsers = count($users);
$adminCount = count(array_filter($users, static fn ($user): bool => $user->role->value === 'admin'));
$memberCount = $totalUsers - $adminCount;
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">CENTRO DE CONTROL</p>
        <h1>Administración</h1>
        <p class="page-description">Una vista general de las cuentas del sistema.</p>
    </div>
    <a class="button-link button-primary" href="/admin/users">Gestionar usuarios <span aria-hidden="true">→</span></a>
</section>

<section class="overview-grid admin-metrics" aria-label="Resumen de usuarios">
    <article class="overview-card overview-featured">
        <span class="overview-icon" aria-hidden="true">▤</span>
        <p class="card-label">CUENTAS REGISTRADAS</p>
        <h2><?= $totalUsers ?></h2>
        <p>En total</p>
    </article>
    <article class="overview-card">
        <span class="overview-icon icon-coral" aria-hidden="true">◆</span>
        <p class="card-label">ADMINISTRADORES</p>
        <h2><?= $adminCount ?></h2>
        <p>Con acceso de gestión</p>
    </article>
    <article class="overview-card">
        <span class="overview-icon icon-lime" aria-hidden="true">●</span>
        <p class="card-label">USUARIOS</p>
        <h2><?= $memberCount ?></h2>
        <p>Con acceso estándar</p>
    </article>
</section>

<section class="data-section">
    <div class="section-heading">
        <div>
            <p class="eyebrow">DIRECTORIO</p>
            <h2>Últimas cuentas</h2>
        </div>
        <a class="text-link" href="/admin/users">Ver todas <span aria-hidden="true">→</span></a>
    </div>
    <div class="table-scroll">
        <table class="users-table">
            <thead>
                <tr><th>Nombre</th><th>Correo</th><th>Rol</th></tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($users, 0, 5) as $user): ?>
                    <tr>
                        <td class="user-name"><?= $this->e($user->name) ?></td>
                        <td><?= $this->e($user->email) ?></td>
                        <td><span class="role-badge role-<?= $this->e($user->role->value) ?>"><?= $user->role->value === 'admin' ? 'Administrador' : 'Usuario' ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($users === []): ?>
                    <tr><td class="empty-state" colspan="3">No hay usuarios registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>