<h1>Crear cuenta</h1>

<?php if (!empty($errors['form'])): ?>
    <p class="alert"><?= $this->e($errors['form']) ?></p>
<?php endif; ?>

<form method="POST" action="/register" novalidate>
    <?= $this->csrfField() ?>

    <label for="name">Nombre</label>
    <input type="text" id="name" name="name"
           value="<?= $this->e($old['name'] ?? '') ?>" required autofocus>
    <?php if (!empty($errors['name'])): ?>
        <small class="field-error"><?= $this->e($errors['name']) ?></small>
    <?php endif; ?>

    <label for="email">Correo electrónico</label>
    <input type="email" id="email" name="email"
           value="<?= $this->e($old['email'] ?? '') ?>" required>
    <?php if (!empty($errors['email'])): ?>
        <small class="field-error"><?= $this->e($errors['email']) ?></small>
    <?php endif; ?>

    <label for="password">Contraseña</label>
    <input type="password" id="password" name="password" required>
    <?php if (!empty($errors['password'])): ?>
        <small class="field-error"><?= $this->e($errors['password']) ?></small>
    <?php endif; ?>

    <label for="password_confirmation">Confirmar contraseña</label>
    <input type="password" id="password_confirmation" name="password_confirmation" required>
    <?php if (!empty($errors['password_confirmation'])): ?>
        <small class="field-error"><?= $this->e($errors['password_confirmation']) ?></small>
    <?php endif; ?>

    <button type="submit">Registrarme</button>
</form>

<p class="link">¿Ya tienes cuenta? <a href="/login">Inicia sesión</a></p>