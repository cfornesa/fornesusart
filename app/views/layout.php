<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Fornesus') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=IM+Fell+English:ital@0;1&family=Courier+Prime&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a href="/" class="site-title">Fornesus Art</a>
        <nav class="site-nav">
            <a href="/" class="<?= ($activePage ?? '') === 'gallery' ? 'active' : '' ?>">Gallery</a>
            <span class="nav-sep">·</span>
            <a href="/categories" class="<?= ($activePage ?? '') === 'categories' ? 'active' : '' ?>">Categories</a>
            <span class="nav-sep">·</span>
            <a href="/about" class="<?= ($activePage ?? '') === 'about' ? 'active' : '' ?>">About</a>
        </nav>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <span>Fornesus Art</span>
    </footer>

    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/cosmos.js" defer></script>
</body>
</html>
