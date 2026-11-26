<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($title) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/app.js" defer></script>
</head>
<body>
    <main class="card<?= !empty($wide) ? ' wide' : '' ?><?= $pageClass !== '' ? ' ' . $this->e($pageClass) : '' ?>">
        <?= $content ?>
    </main>
</body>
</html>