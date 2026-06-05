<?php

declare(strict_types=1);

class PageController
{
    public static function show(string $slug): void
    {
        $page = Page::safeFindPublishedBySlug($slug);
        if (!$page) {
            http_response_code(404);
            require dirname(__DIR__) . '/views/404.php';
            return;
        }

        $sections = PageSection::allForPage((int) $page['id']);
        $contactSent = $_GET['sent'] ?? false;
        $contactError = null;
        require dirname(__DIR__) . '/views/page.php';
    }

    public static function contact(): void
    {
        $page = Page::safeFindPublishedBySlug('contact');
        if (!$page) {
            AboutController::contact();
            return;
        }

        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$name || !$email || !$message || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $sections     = PageSection::allForPage((int) $page['id']);
            $contactSent  = false;
            $contactError = 'Please fill all fields with a valid email.';
            require dirname(__DIR__) . '/views/page.php';
            return;
        }

        $stmt = db()->prepare(
            'INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $email, $message]);

        header('Location: /contact?sent=1');
        exit;
    }

    public static function legacyAbout(): void
    {
        if (!Page::safeFindPublishedBySlug('bio')) {
            AboutController::index();
            return;
        }

        header('Location: /bio', true, 301);
        exit;
    }

    public static function contactPage(): void
    {
        if (!Page::safeFindPublishedBySlug('contact')) {
            AboutController::index();
            return;
        }

        self::show('contact');
    }
}
