<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $siteName = 'Fornesus Art';
    $resolvedTitle = $metaTitle ?? $pageTitle ?? $siteName;
    $resolvedDescription = $metaDescription ?? 'An otherworldly archive of artworks, exhibits, and collected pages from Fornesus Art.';
    $resolvedOgTitle = $ogTitle ?? $resolvedTitle;
    $resolvedOgDescription = $ogDescription ?? $resolvedDescription;
    $resolvedCanonical = $canonicalUrl ?? seo_current_url();
    $resolvedImage = seo_absolute_url($metaImage ?? null);
    $resolvedImageAlt = $metaImageAlt ?? $resolvedOgTitle;
    $navPages = Page::navItems();
    $showLegacyAboutLink = empty($navPages);
    ?>
    <title><?= htmlspecialchars($resolvedTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($resolvedDescription) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($resolvedCanonical) ?>">
    <?php if (!empty($metaRobots)): ?>
        <meta name="robots" content="<?= htmlspecialchars($metaRobots) ?>">
    <?php endif ?>
    <meta property="og:site_name" content="<?= htmlspecialchars($siteName) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($resolvedOgTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($resolvedOgDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($resolvedCanonical) ?>">
    <meta name="twitter:card" content="<?= $resolvedImage ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($resolvedOgTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($resolvedOgDescription) ?>">
    <meta name="theme-color" content="#000000">
    <?php if ($resolvedImage): ?>
        <meta property="og:image" content="<?= htmlspecialchars($resolvedImage) ?>">
        <meta property="og:image:alt" content="<?= htmlspecialchars($resolvedImageAlt) ?>">
        <meta name="twitter:image" content="<?= htmlspecialchars($resolvedImage) ?>">
        <meta name="twitter:image:alt" content="<?= htmlspecialchars($resolvedImageAlt) ?>">
    <?php endif ?>
    <script>document.documentElement.classList.add('js-enhanced');</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Lora:ital,wght@0,400..700;1,400..700&family=Courier+Prime&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(dirname(__DIR__, 2) . '/public/assets/css/style.css') ?>">
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to content</a>
    <header class="site-header">
        <a href="/" class="site-title"><?= htmlspecialchars($siteName) ?></a>
        <nav class="site-nav" aria-label="Primary">
            <a href="/" class="<?= ($activePage ?? '') === 'gallery' ? 'active' : '' ?>"<?= ($activePage ?? '') === 'gallery' ? ' aria-current="page"' : '' ?>>Gallery</a>
            <span class="nav-sep" aria-hidden="true">·</span>
            <a href="/categories" class="<?= ($activePage ?? '') === 'categories' ? 'active' : '' ?>"<?= ($activePage ?? '') === 'categories' ? ' aria-current="page"' : '' ?>>Categories</a>
            <?php if ($showLegacyAboutLink): ?>
                <span class="nav-sep" aria-hidden="true">·</span>
                <a href="/about" class="<?= ($activePage ?? '') === 'about' ? 'active' : '' ?>"<?= ($activePage ?? '') === 'about' ? ' aria-current="page"' : '' ?>>About</a>
            <?php endif ?>
            <?php foreach ($navPages as $navPage): ?>
                <span class="nav-sep" aria-hidden="true">·</span>
                <a href="/<?= htmlspecialchars($navPage['slug']) ?>"
                   class="<?= ($activePage ?? '') === $navPage['slug'] ? 'active' : '' ?>"<?= ($activePage ?? '') === $navPage['slug'] ? ' aria-current="page"' : '' ?>>
                    <?= htmlspecialchars($navPage['nav_label'] ?: $navPage['title']) ?>
                </a>
            <?php endforeach ?>
        </nav>
    </header>

    <main id="main-content">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <span>Fornesus Art</span>
    </footer>

    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/cosmos.js" defer></script>
</body>
</html>
