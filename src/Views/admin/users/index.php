<div class="admin-heading">
    <div>
        <p class="eyebrow">Administración</p>
        <h1>Usuarios</h1>
    </div>
    <a class="text-link" href="/dashboard">Volver al panel</a>
</div>

<?php if (!empty($error)): ?>
    <p class="alert"><?= $this->e($error) ?></p>
<?php endif; ?>

<div class="admin-toolbar">
    <p class="admin-summary">Cuentas registradas <strong><?= count($users) ?></strong></p>
    <a class="button-link button-primary" href="/admin/users/create">+ Crear usuario</a>
</div>

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