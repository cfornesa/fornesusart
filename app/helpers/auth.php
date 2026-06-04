<?php

declare(strict_types=1);

function admin_check(): void
{
    if (empty($_SESSION['admin_authed'])) {
        header('Location: /admin/login');
        exit;
    }
}

function admin_login(string $password): bool
{
    $hash = $_ENV['ADMIN_PASSWORD_HASH'] ?? '';
    if ($hash && password_verify($password, $hash)) {
        $_SESSION['admin_authed'] = true;
        return true;
    }
    return false;
}

function admin_logout(): void
{
    unset($_SESSION['admin_authed']);
    session_destroy();
}
