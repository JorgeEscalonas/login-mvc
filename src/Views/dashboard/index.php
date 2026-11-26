<?php
$name = $name ?? 'Usuario';
$role = $role ?? 'user';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">RESUMEN DE CUENTA</p>
        <h1>Hola, <?= $this->e($name) ?></h1>
        <p class="page-description">Tu espacio de trabajo está listo.</p>
    </div>
    <span class="status-pill"><span></span> Sesión activa</span>
</section>

<section class="overview-grid" aria-label="Resumen de cuenta">
    <article class="overview-card overview-featured">
        <span class="overview-icon" aria-hidden="true">✓</span>
        <p class="card-label">ESTADO DE CUENTA</p>
        <h2>Activa</h2>
        <p>Tu acceso está disponible.</p>
    </article>
    <article class="overview-card">
        <span class="overview-icon icon-coral" aria-hidden="true">◎</span>
        <p class="card-label">PERFIL</p>
        <h2><?= $this->e($name) ?></h2>
        <p>Cuenta personal</p>
    </article>
    <article class="overview-card">
        <span class="overview-icon icon-lime" aria-hidden="true">◇</span>
        <p class="card-label">NIVEL DE ACCESO</p>
        <h2><?= $role === 'admin' ? 'Administrador' : 'Usuario' ?></h2>
        <p><?= $role === 'admin' ? 'Acceso de administración habilitado' : 'Acceso estándar' ?></p>
    </article>
</section>

<?php if ($role === 'admin'): ?>
    <section class="quick-access">
        <div>
            <p class="eyebrow">HERRAMIENTAS</p>
            <h2>Administración de usuarios</h2>
            <p>Consulta cuentas, actualiza perfiles y gestiona roles.</p>
        </div>
        <a class="button-link button-primary" href="/admin/users">Abrir usuarios <span aria-hidden="true">→</span></a>
    </section>
<?php endif; ?>