<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin — Fornesus Art') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=IM+Fell+English:ital@0;1&family=Courier+Prime&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/css/style.css') ?>">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/css/admin.css') ?>">

</head>
<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin';
$bodyClass = trim('admin-body ' . ($bodyClass ?? ''));
$mainClass = trim('admin-main ' . ($mainClass ?? ''));

$adminNavItems = [
    '/admin' => 'Dashboard',
    '/admin/artworks' => 'Works',
    '/admin/categories' => 'Categories',
    '/admin/exhibits' => 'Exhibits',
    '/admin/media' => 'Media',
    '/admin/trash' => 'Trash',
    '/admin/bio' => 'Bio',
    '/admin/messages' => 'Messages',
];
?>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <header class="admin-header">
        <div class="admin-brand">
            <span class="admin-kicker">Administration</span>
            <a href="/admin" class="admin-site-link">Fornesus Archive</a>
        </div>
        <nav class="admin-nav">
            <?php foreach ($adminNavItems as $href => $label): ?>
                <?php $isActive = $currentPath === $href || ($href !== '/admin' && str_starts_with($currentPath, $href . '/')); ?>
                <a href="<?= $href ?>" class="<?= $isActive ? 'active' : '' ?>"<?= $isActive ? ' aria-current="page"' : '' ?>><?= $label ?></a>
            <?php endforeach ?>
            <a href="/admin/logout" class="admin-logout">Logout</a>
        </nav>
    </header>

    <main class="<?= htmlspecialchars($mainClass) ?>">
        <?= $content ?>
    </main>

    <script src="/assets/js/main.js"></script>
</body>
</html>
