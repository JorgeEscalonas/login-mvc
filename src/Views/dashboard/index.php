<h1>Bienvenido, <?= $this->e($name) ?></h1>
<p>Has iniciado sesión correctamente.</p>
<p>Rol: <strong><?= $this->e($role) ?></strong></p>

<?php if ($role === 'admin'): ?>
    <p><a class="button-link button-primary" href="/admin/users">Administrar usuarios</a></p>
<?php endif; ?>

<form method="POST" action="/logout">
    <?= $this->csrfField() ?>
    <button type="submit">Cerrar sesión</button>
</form>