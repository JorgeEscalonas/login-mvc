<?php
$email = $email ?? '';
$token = $token ?? '';
$stage = $stage ?? 'email';
$notice = $notice ?? null;
$errors = $errors ?? [];
?>

<h1><?= $stage === 'token' ? 'Verificar token' : 'Recuperar contraseña' ?></h1>

<?php if (!empty($notice)): ?>
    <p class="alert"><?= $this->e($notice) ?></p>
<?php endif; ?>

<?php if (!empty($errors['form'])): ?>
    <p class="alert"><?= $this->e($errors['form']) ?></p>
<?php endif; ?>

<form method="POST" action="<?= $stage === 'token' ? '/verify-reset-token' : '/forgot-password' ?>" novalidate>
    <?= $this->csrfField() ?>
    <label for="email">Correo electrónico</label>
    <input type="email" id="email" name="email" value="<?= $this->e($email) ?>" required autocomplete="email"<?= $stage === 'token' ? ' readonly' : '' ?>>

    <?php if ($stage === 'token'): ?>
        <label for="token">Token de recuperación</label>
        <input type="text" id="token" name="token" value="<?= $this->e($token) ?>" required autocomplete="off">
        <button type="submit">Verificar token</button>
    <?php else: ?>
        <button type="submit">Solicitar recuperación</button>
    <?php endif; ?>
</form>

<p class="link"><a href="/login">Volver a iniciar sesión</a></p>