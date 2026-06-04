<?php

declare(strict_types=1);

class AboutController
{
    public static function index(): void
    {
        $sections      = BioSection::all();
        $contactSent   = $_GET['sent'] ?? false;
        $contactError  = null;
        require dirname(__DIR__) . '/views/about.php';
    }

    public static function contact(): void
    {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$name || !$email || !$message || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $sections     = BioSection::all();
            $contactSent  = false;
            $contactError = 'Please fill all fields with a valid email.';
            require dirname(__DIR__) . '/views/about.php';
            return;
        }

        $stmt = db()->prepare(
            'INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $email, $message]);

        header('Location: /about?sent=1');
        exit;
    }
}
