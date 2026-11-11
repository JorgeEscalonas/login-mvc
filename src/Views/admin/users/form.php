<?php
$name = $old['name'] ?? $user?->name ?? '';
$email = $old['email'] ?? $user?->email ?? '';
$role = $old['role'] ?? $user?->role->value ?? 'user';
?>

<div class="admin-heading">
    <div>
        <p class="eyebrow">Administración</p>
        <h1><?= $this->e($title) ?></h1>
    </div>
    <a class="text-link" href="/admin/users">Volver a usuarios</a>
</div>

<?php if (!empty($errors['form'])): ?>
    <p class="alert"><?= $this->e($errors['form']) ?></p>
<?php endif; ?>

<form class="admin-form" method="POST" action="<?= $this->e($action) ?>" novalidate>
    <?= $this->csrfField() ?>

    <div class="form-grid">
        <div class="form-field">
            <label for="name">Nombre</label>
            <input type="text" id="name" name="name" value="<?= $this->e($name) ?>" required autofocus>
            <?php if (!empty($errors['name'])): ?>
                <small class="field-error"><?= $this->e($errors['name']) ?></small>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" value="<?= $this->e($email) ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <small class="field-error"><?= $this->e($errors['email']) ?></small>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="role">Rol</label>
            <select id="role" name="role" required>
                <option value="user"<?= $role === 'user' ? ' selected' : '' ?>>Usuario</option>
                <option value="admin"<?= $role === 'admin' ? ' selected' : '' ?>>Administrador</option>
            </select>
            <?php if (!empty($errors['role'])): ?>
                <small class="field-error"><?= $this->e($errors['role']) ?></small>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="password">Contraseña</label>
            <?php if ($user !== null): ?>
                <small class="field-hint">Déjala vacía para conservar la contraseña actual.</small>
            <?php endif; ?>
            <input type="password" id="password" name="password"<?= $user === null ? ' required' : '' ?> autocomplete="new-password">
            <?php if (!empty($errors['password'])): ?>
                <small class="field-error"><?= $this->e($errors['password']) ?></small>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation"<?= $user === null ? ' required' : '' ?> autocomplete="new-password">
            <?php if (!empty($errors['password_confirmation'])): ?>
                <small class="field-error"><?= $this->e($errors['password_confirmation']) ?></small>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <button class="button-primary" type="submit">Guardar usuario</button>
        <a class="button-secondary" href="/admin/users">Cancelar</a>
    </div>
</form>