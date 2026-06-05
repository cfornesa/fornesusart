<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Fornesus Art</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Lora:ital,wght@0,400..700;1,400..700&family=Courier+Prime&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-body admin-login-body">
    <div class="login-wrap">
        <h1 class="login-title">Archive Access</h1>
        <?php if ($error): ?>
            <p class="login-error" role="alert">Incorrect passphrase.</p>
        <?php endif ?>
        <form method="POST" action="/admin/login" class="login-form">
            <label for="admin-password">Passphrase</label>
            <input
                type="password"
                id="admin-password"
                name="password"
                placeholder="Passphrase"
                autocomplete="current-password"
                required
                autofocus
            >
            <button type="submit">Enter</button>
        </form>
    </div>
</body>
</html>
