<?php
$title = $title ?? 'Sistema de Gestión y Control Integral';
$content = $content ?? '';
$pageClass = $pageClass ?? '';
$appShell = $appShell ?? false;
$activePath = $activePath ?? '/';
$navName = $navName ?? 'Usuario';
$navRole = $navRole ?? 'user';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($title) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/app.js" defer></script>
</head>
<body class="<?= $appShell ? 'app-body' : 'auth-body' ?>">
<?php if ($appShell): ?>
    <div class="app-shell">
        <aside class="sidebar" id="app-sidebar">
            <a class="brand" href="/dashboard" aria-label="Sistema de gestión y control integral, ir al panel">
                <span class="brand-mark">G</span>
                <span class="brand-name">Sistema de Gestión<span>y Control Integral</span></span>
            </a>

            <p class="sidebar-label">OPERACIÓN DEL CINE</p>
            <nav class="sidebar-nav" aria-label="Navegación principal">
                <a class="nav-link<?= $activePath === '/dashboard' ? ' is-active' : '' ?>" href="/dashboard">
                    <span class="nav-symbol" aria-hidden="true">⌂</span>
                    <span>Resumen</span>
                </a>
                <?php if ($navRole === 'admin'): ?>
                    <a class="nav-link<?= str_starts_with($activePath, '/admin') ? ' is-active' : '' ?>" href="/admin/users">
                        <span class="nav-symbol" aria-hidden="true">▤</span>
                        <span>Usuarios</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-bottom">
                <div class="sidebar-account">
                    <span class="account-avatar"><?= $this->e(mb_strtoupper(mb_substr($navName, 0, 1))) ?></span>
                    <span class="account-copy">
                        <strong><?= $this->e($navName) ?></strong>
                        <small><?= $navRole === 'admin' ? 'Administrador' : 'Usuario' ?></small>
                    </span>
                </div>
                <form method="POST" action="/logout">
                    <?= $this->csrfField() ?>
                    <button class="sidebar-logout" type="submit"><span aria-hidden="true">↗</span> Cerrar sesión</button>
                </form>
            </div>
        </aside>
        <button class="sidebar-overlay" type="button" data-sidebar-close aria-label="Cerrar navegación"></button>

        <div class="app-main">
            <header class="topbar">
                <button class="menu-toggle" type="button" aria-label="Abrir navegación" aria-expanded="false" aria-controls="app-sidebar">
                    <span aria-hidden="true">☰</span>
                </button>
                <div class="breadcrumb"><span>Cine</span><span aria-hidden="true">/</span><strong><?= $this->e($title) ?></strong></div>
                <span class="topbar-status"><span></span> Cuenta activa</span>
            </header>
            <main class="app-content<?= $pageClass !== '' ? ' ' . $this->e($pageClass) : '' ?>">
                <?= $content ?>
            </main>
        </div>
    </div>
<?php else: ?>
    <main class="auth-layout">
        <section class="auth-aside">
            <a class="brand" href="/login" aria-label="Sistema de Gestión y Control Integral">
                <span class="brand-mark">G</span>
                <span class="brand-name">Sistema de Gestión<span>y Control Integral</span></span>
            </a>
            <div class="auth-message">
                <p class="auth-kicker"><span></span> PLATAFORMA DE GESTIÓN</p>
                <h2>Acceso claro. Gestión bajo control.</h2>
                <p>Ingresa para continuar con las herramientas disponibles para tu perfil.</p>
            </div>
            <div class="auth-footer"><span>SGCI</span> Sistema de Gestión y Control Integral <span>·</span> Acceso seguro</div>
        </section>
        <section class="auth-panel">
            <div class="auth-form-wrap">
                <div class="mobile-brand"><span class="brand-mark">G</span><span>Sistema de Gestión y Control Integral</span></div>
                <?= $content ?>
            </div>
        </section>
    </main>
<?php endif; ?>
</body>
</html>