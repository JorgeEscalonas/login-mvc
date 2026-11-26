<?php
$email = $email ?? '';
$token = $token ?? '';
$notice = $notice ?? null;
$errors = $errors ?? [];
?>

<h1>Crear nueva contraseña</h1>

<?php if (!empty($notice)): ?>
    <p class="alert"><?= $this->e($notice) ?></p>
<?php endif; ?>

<?php if (!empty($errors['form'])): ?>
    <p class="alert"><?= $this->e($errors['form']) ?></p>
<?php endif; ?>

<form method="POST" action="/reset-password" novalidate>
    <?= $this->csrfField() ?>
    <input type="hidden" name="email" value="<?= $this->e($email) ?>">
    <input type="hidden" name="token" value="<?= $this->e($token) ?>">

    <label for="password">Nueva contraseña</label>
    <input type="password" id="password" name="password" required autocomplete="new-password">
    <?php if (!empty($errors['password'])): ?>
        <small class="field-error"><?= $this->e($errors['password']) ?></small>
    <?php endif; ?>

    <label for="password_confirmation">Confirmar contraseña</label>
    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
    <?php if (!empty($errors['password_confirmation'])): ?>
        <small class="field-error"><?= $this->e($errors['password_confirmation']) ?></small>
    <?php endif; ?>

    <button type="submit">Actualizar contraseña</button>
</form>

<p class="link"><a href="/login">Volver a iniciar sesión</a></p>
<p class="link"><a href="/forgot-password">Solicitar otro token</a></p>