<?php
$users = $users ?? [];
$currentUserId = $currentUserId ?? 0;
$error = $error ?? null;
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">CENTRO DE CONTROL</p>
        <h1>Usuarios</h1>
        <p class="page-description">Administra cuentas, perfiles y permisos.</p>
    </div>
    <a class="button-link button-primary" href="/admin/users/create"><span aria-hidden="true">＋</span> Crear usuario</a>
</div>

<?php if (!empty($error)): ?>
    <p class="alert"><?= $this->e($error) ?></p>
<?php endif; ?>

<p class="admin-summary">Cuentas registradas <strong><?= count($users) ?></strong></p>

<div class="table-scroll">
    <table class="users-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users === []): ?>
                <tr>
                    <td class="empty-state" colspan="4">No hay usuarios registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="user-name"><?= $this->e($user->name) ?></td>
                        <td><?= $this->e($user->email) ?></td>
                        <td>
                            <span class="role-badge role-<?= $this->e($user->role->value) ?>">
                                <?= $user->role->value === 'admin' ? 'Administrador' : 'Usuario' ?>
                            </span>
                        </td>
                        <td>
                            <div class="user-actions">
                                <a class="table-link" href="/admin/users/edit?id=<?= $user->id ?>">Editar</a>
                                <form class="inline-form" method="POST" action="/admin/users/delete">
                                    <?= $this->csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $user->id ?>">
                                    <button class="button-danger" type="submit"<?= $user->id === $currentUserId ? ' disabled' : '' ?>>Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>