<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin — Fornesus Art') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=IM+Fell+English:ital@0;1&family=Courier+Prime&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <a href="/" class="admin-site-link">Fornesus</a>
        <nav class="admin-nav">
            <a href="/admin">Dashboard</a>
            <a href="/admin/artworks">Works</a>
            <a href="/admin/categories">Categories</a>
            <a href="/admin/exhibits">Exhibits</a>
            <a href="/admin/media">Media</a>
            <a href="/admin/trash">Trash</a>
            <a href="/admin/bio">Bio</a>
            <a href="/admin/messages">Messages</a>
            <a href="/admin/logout" class="admin-logout">Logout</a>
        </nav>
    </header>

    <main class="admin-main">
        <?= $content ?>
    </main>

    <script src="/assets/js/main.js"></script>
</body>
</html>
