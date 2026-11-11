<h1>Iniciar sesión</h1>

<?php if (!empty($error)): ?>
    <p class="alert"><?= $this->e($error) ?></p>
<?php endif; ?>

<form method="POST" action="/login" novalidate>
    <?= $this->csrfField() ?>
    <label for="email">Correo electrónico</label>
    <input type="email" id="email" name="email"
           value="<?= $this->e($email ?? '') ?>" required autofocus>

    <label for="password">Contraseña</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Entrar</button>
    <p class="link">¿No tienes cuenta? <a href="/register">Regístrate</a></p>
</form>