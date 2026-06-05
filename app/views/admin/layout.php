<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin — Fornesus Art') ?></title>
    <link rel="preload" href="/assets/fonts/lora-normal-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/assets/fonts/pinyon-script-latin.woff2" as="font" type="font/woff2" crossorigin>
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
    '/admin/pages' => 'Pages',
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
