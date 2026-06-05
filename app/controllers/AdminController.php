<?php

declare(strict_types=1);

class AdminController
{
    // ── Auth ──────────────────────────────────────────────────────────────

    public static function loginForm(): void
    {
        if (!empty($_SESSION['admin_authed'])) {
            header('Location: /admin');
            exit;
        }
        $error = $_GET['error'] ?? null;
        require dirname(__DIR__) . '/views/admin/login.php';
    }

    public static function loginSubmit(): void
    {
        if (admin_login($_POST['password'] ?? '')) {
            header('Location: /admin');
        } else {
            header('Location: /admin/login?error=1');
        }
        exit;
    }

    public static function logout(): void
    {
        admin_logout();
        header('Location: /admin/login');
        exit;
    }

    // ── Dashboard ─────────────────────────────────────────────────────────

    public static function dashboard(): void
    {
        admin_check();
        $artworkCount  = (int) db()->query('SELECT COUNT(*) FROM artworks  WHERE deleted_at IS NULL')->fetchColumn();
        $categoryCount = (int) db()->query('SELECT COUNT(*) FROM categories WHERE deleted_at IS NULL')->fetchColumn();
        $exhibitCount  = (int) db()->query('SELECT COUNT(*) FROM exhibits  WHERE deleted_at IS NULL')->fetchColumn();
        $messageCount  = (int) db()->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
        $trashCount    = Artwork::trashedCount() + Category::trashedCount() + Exhibit::trashedCount() + MediaFile::trashedCount();
        require dirname(__DIR__) . '/views/admin/dashboard.php';
    }

    // ── Artworks ──────────────────────────────────────────────────────────

    public static function artworksIndex(): void
    {
        admin_check();
        $artworks   = Artwork::all();
        $categories = Category::all();
        require dirname(__DIR__) . '/views/admin/artworks/index.php';
    }

    public static function artworkCreate(): void
    {
        admin_check();
        $categories = Category::all();
        $artwork    = null;
        $error      = null;
        require dirname(__DIR__) . '/views/admin/artworks/form.php';
    }

    public static function artworkStore(): void
    {
        admin_check();

        try {
            $data = self::resolveArtworkData(null);
            Artwork::create($data);
            header('Location: /admin/artworks');
        } catch (Throwable $e) {
            $categories = Category::all();
            $artwork    = null;
            $error      = $e->getMessage();
            require dirname(__DIR__) . '/views/admin/artworks/form.php';
        }
        exit;
    }

    public static function artworkEdit(string $id): void
    {
        admin_check();
        $artwork = Artwork::find((int) $id);
        if (!$artwork) {
            header('Location: /admin/artworks');
            exit;
        }
        $categories = Category::all();
        $error      = null;
        require dirname(__DIR__) . '/views/admin/artworks/form.php';
    }

    public static function artworkUpdate(string $id): void
    {
        admin_check();

        try {
            $data = self::resolveArtworkData((int) $id);
            Artwork::update((int) $id, $data);
            header('Location: /admin/artworks');
        } catch (Throwable $e) {
            $artwork    = Artwork::find((int) $id);
            $categories = Category::all();
            $error      = $e->getMessage();
            require dirname(__DIR__) . '/views/admin/artworks/form.php';
        }
        exit;
    }

    public static function artworkDelete(string $id): void
    {
        admin_check();
        Artwork::softDelete((int) $id);
        header('Location: /admin/artworks');
        exit;
    }

    public static function artworkReorder(): void
    {
        admin_check();
        $ids = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));
        $stmt = db()->prepare('UPDATE artworks SET sort_order = ? WHERE id = ?');
        foreach (array_values($ids) as $i => $id) {
            $stmt->execute([$i, $id]);
        }
        header('Content-Type: application/json');
        echo '{"ok":true}';
        exit;
    }

    public static function categoryReorder(): void
    {
        admin_check();
        $ids = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));
        $stmt = db()->prepare('UPDATE categories SET sort_order = ? WHERE id = ?');
        foreach (array_values($ids) as $i => $id) {
            $stmt->execute([$i, $id]);
        }
        header('Content-Type: application/json');
        echo '{"ok":true}';
        exit;
    }

    public static function artworkOrder(string $id): void
    {
        admin_check();
        $sort = (int) ($_POST['sort_order'] ?? 0);
        $stmt = db()->prepare('UPDATE artworks SET sort_order = ? WHERE id = ?');
        $stmt->execute([$sort, (int) $id]);
        header('Location: /admin/artworks');
        exit;
    }

    // ── Categories ────────────────────────────────────────────────────────

    public static function categoriesIndex(): void
    {
        admin_check();
        $categories = Category::all();
        require dirname(__DIR__) . '/views/admin/categories/index.php';
    }

    public static function categoryCreate(): void
    {
        admin_check();
        $category = null;
        $error    = null;
        require dirname(__DIR__) . '/views/admin/categories/form.php';
    }

    public static function categoryStore(): void
    {
        admin_check();
        $name = trim($_POST['name'] ?? '');
        if (!$name) {
            $category = null;
            $error    = 'Name is required.';
            require dirname(__DIR__) . '/views/admin/categories/form.php';
            return;
        }
        $postedSlug = trim($_POST['slug'] ?? '');
        $slug       = $postedSlug ? slugify($postedSlug) : unique_category_slug($name);
        try {
            [$thumbType, $thumbValue] = self::resolveThumbnail(null, 'category');
        } catch (\RuntimeException $e) {
            $category = null;
            $error    = $e->getMessage();
            require dirname(__DIR__) . '/views/admin/categories/form.php';
            return;
        }
        $desc = trim($_POST['description'] ?? '') ?: null;
        Category::create($name, $slug, 0, $thumbType ?: null, $thumbValue ?: null, $desc);
        header('Location: /admin/categories');
        exit;
    }

    public static function categoryEdit(string $id): void
    {
        admin_check();
        $category = Category::find((int) $id);
        if (!$category) {
            header('Location: /admin/categories');
            exit;
        }
        $error = null;
        require dirname(__DIR__) . '/views/admin/categories/form.php';
    }

    public static function categoryUpdate(string $id): void
    {
        admin_check();
        $existing = Category::find((int) $id);
        $name     = trim($_POST['name'] ?? '');
        if (!$name) {
            $category = $existing;
            $error    = 'Name is required.';
            require dirname(__DIR__) . '/views/admin/categories/form.php';
            return;
        }
        $postedSlug = trim($_POST['slug'] ?? '');
        $slug       = $postedSlug ? slugify($postedSlug) : unique_category_slug($name, (int) $id);
        try {
            [$thumbType, $thumbValue] = self::resolveThumbnail($existing, 'category');
        } catch (\RuntimeException $e) {
            $category = $existing;
            $error    = $e->getMessage();
            require dirname(__DIR__) . '/views/admin/categories/form.php';
            return;
        }
        $desc = trim($_POST['description'] ?? '') ?: null;
        Category::update((int) $id, $name, $slug, (int) ($existing['sort_order'] ?? 0), $thumbType ?: null, $thumbValue ?: null, $desc);
        header('Location: /admin/categories');
        exit;
    }

    public static function categoryDelete(string $id): void
    {
        admin_check();
        Category::softDelete((int) $id);
        header('Location: /admin/categories');
        exit;
    }

    // ── Pages ─────────────────────────────────────────────────────────────

    public static function pagesLegacyRedirect(): void
    {
        admin_check();
        header('Location: /admin/pages', true, 302);
        exit;
    }

    public static function pagesIndex(): void
    {
        admin_check();
        $pages = Page::all();
        require dirname(__DIR__) . '/views/admin/pages/index.php';
    }

    public static function pageCreate(): void
    {
        admin_check();
        $page = null;
        $pageError = null;
        require dirname(__DIR__) . '/views/admin/pages/form.php';
    }

    public static function pageStore(): void
    {
        admin_check();

        try {
            $data = self::resolvePageData(null);
            $pageId = Page::create($data);
            header('Location: /admin/pages/' . $pageId . '/edit');
        } catch (Throwable $e) {
            $page = null;
            $pageError = $e->getMessage();
            require dirname(__DIR__) . '/views/admin/pages/form.php';
        }
        exit;
    }

    public static function pageEdit(string $id): void
    {
        admin_check();
        $page = Page::find((int) $id);
        if (!$page) {
            header('Location: /admin/pages');
            exit;
        }

        $sections = PageSection::allForPage((int) $id);
        $pageError = null;
        require dirname(__DIR__) . '/views/admin/pages/form.php';
    }

    public static function pageUpdate(string $id): void
    {
        admin_check();
        $page = Page::find((int) $id);
        if (!$page) {
            header('Location: /admin/pages');
            exit;
        }

        try {
            $data = self::resolvePageData((int) $id);
            Page::update((int) $id, $data);
            header('Location: /admin/pages/' . (int) $id . '/edit?saved=1');
        } catch (Throwable $e) {
            $page = array_merge($page, $_POST);
            $sections = PageSection::allForPage((int) $id);
            $pageError = $e->getMessage();
            require dirname(__DIR__) . '/views/admin/pages/form.php';
        }
        exit;
    }

    public static function pageDelete(string $id): void
    {
        admin_check();
        Page::softDelete((int) $id);
        header('Location: /admin/pages');
        exit;
    }

    public static function pagesTrash(): void
    {
        admin_check();
        $pages = Page::trashed();
        require dirname(__DIR__) . '/views/admin/pages/trash.php';
    }

    public static function pageRestore(string $id): void
    {
        admin_check();
        Page::restore((int) $id);
        header('Location: /admin/pages/trash');
        exit;
    }

    public static function pageHardDelete(string $id): void
    {
        admin_check();
        Page::hardDelete((int) $id);
        header('Location: /admin/pages/trash');
        exit;
    }

    public static function pageToggleNav(string $id): void
    {
        admin_check();
        $showInNav = Page::toggleNav((int) $id);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'show_in_nav' => (int) $showInNav]);
        exit;
    }

    public static function pagesTrashEmpty(): void
    {
        admin_check();
        foreach (Page::trashed() as $page) {
            Page::hardDelete((int) $page['id']);
        }
        header('Location: /admin/pages/trash');
        exit;
    }

    public static function pageReorder(): void
    {
        admin_check();
        $ids = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));
        Page::reorder($ids);
        header('Content-Type: application/json');
        echo '{"ok":true}';
        exit;
    }

    public static function pageSectionCreate(string $pageId): void
    {
        admin_check();
        $page = Page::find((int) $pageId);
        if (!$page) {
            header('Location: /admin/pages');
            exit;
        }

        $section = null;
        $sectionError = null;
        require dirname(__DIR__) . '/views/admin/pages/section-form.php';
    }

    public static function pageSectionStore(string $pageId): void
    {
        admin_check();
        $page = Page::find((int) $pageId);
        if (!$page) {
            header('Location: /admin/pages');
            exit;
        }

        $heading = trim($_POST['heading'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            $section = null;
            $sectionError = 'Content is required.';
            require dirname(__DIR__) . '/views/admin/pages/section-form.php';
            return;
        }

        PageSection::create((int) $pageId, $heading, $content);
        header('Location: /admin/pages/' . (int) $pageId . '/edit');
        exit;
    }

    public static function pageSectionEdit(string $sectionId): void
    {
        admin_check();
        $section = PageSection::find((int) $sectionId);
        if (!$section) {
            header('Location: /admin/pages');
            exit;
        }
        $page = Page::find((int) $section['page_id']);
        $sectionError = null;
        require dirname(__DIR__) . '/views/admin/pages/section-form.php';
    }

    public static function pageSectionUpdate(string $sectionId): void
    {
        admin_check();
        $section = PageSection::find((int) $sectionId);
        if (!$section) {
            header('Location: /admin/pages');
            exit;
        }

        $page = Page::find((int) $section['page_id']);
        $heading = trim($_POST['heading'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            $sectionError = 'Content is required.';
            require dirname(__DIR__) . '/views/admin/pages/section-form.php';
            return;
        }

        PageSection::update((int) $sectionId, $heading, $content);
        header('Location: /admin/pages/' . (int) $section['page_id'] . '/edit');
        exit;
    }

    public static function pageSectionDelete(string $sectionId): void
    {
        admin_check();
        $section = PageSection::find((int) $sectionId);
        if ($section) {
            PageSection::delete((int) $sectionId);
            header('Location: /admin/pages/' . (int) $section['page_id'] . '/edit');
            exit;
        }

        header('Location: /admin/pages');
        exit;
    }

    public static function pageSectionReorder(string $pageId): void
    {
        admin_check();
        $ids = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));
        PageSection::reorder((int) $pageId, $ids);
        header('Content-Type: application/json');
        echo '{"ok":true}';
        exit;
    }

    // ── Messages ──────────────────────────────────────────────────────────

    public static function messagesIndex(): void
    {
        admin_check();
        $messages = db()->query(
            'SELECT * FROM contact_messages ORDER BY created_at DESC'
        )->fetchAll();
        require dirname(__DIR__) . '/views/admin/messages.php';
    }

    // ── Internal ──────────────────────────────────────────────────────────

    private static function resolveArtworkData(?int $existingId): array
    {
        $title      = trim($_POST['title'] ?? '');
        $year       = trim($_POST['year'] ?? '');
        $desc       = trim($_POST['description'] ?? '');
        $catId      = (int) ($_POST['category_id'] ?? 0);
        $sort       = (int) ($_POST['sort_order'] ?? 0);

        if (!$title) {
            throw new InvalidArgumentException('Title is required.');
        }

        // Slug: use posted slug if provided, else auto-generate
        $postedSlug = trim($_POST['slug'] ?? '');
        $slug = $postedSlug
            ? slugify($postedSlug)
            : ($existingId
                ? (Artwork::find($existingId)['slug'] ?? unique_slug($title, $existingId))
                : unique_slug($title));

        // Thumbnail — type is always 'link' now (uploaded images use /image/{id} URLs)
        $thumbType = $_POST['thumbnail_type'] ?? 'link';
        if ($thumbType === 'upload' && !empty($_FILES['thumbnail_upload']['name'])) {
            $thumbValue = upload_image($_FILES['thumbnail_upload'], 'thumbnails');
        } elseif ($thumbType === 'link') {
            $thumbValue = trim($_POST['thumbnail_link'] ?? '') ?: null;
            if ($thumbValue === null) $thumbType = null;
        } else {
            $thumbType  = null;
            $thumbValue = null;
        }

        // Piece
        $pieceType = $_POST['piece_type'] ?? 'image_link';
        switch ($pieceType) {
            case 'image_upload':
                if (!empty($_FILES['piece_upload']['name'])) {
                    $pieceValue = upload_image($_FILES['piece_upload'], 'pieces');
                } elseif ($existingId) {
                    $existing   = Artwork::find($existingId);
                    $pieceType  = $existing['piece_type'];
                    $pieceValue = $existing['piece_value'];
                } else {
                    throw new InvalidArgumentException('An image file is required.');
                }
                break;
            case 'image_link':
                $pieceValue = trim($_POST['piece_link'] ?? '');
                if (!$pieceValue && $existingId) {
                    $pieceValue = Artwork::find($existingId)['piece_value'];
                }
                if (!$pieceValue) {
                    throw new InvalidArgumentException('An image URL is required.');
                }
                break;
            case 'embed':
                $pieceValue = trim($_POST['piece_embed'] ?? '');
                if (!$pieceValue && $existingId) {
                    $pieceValue = Artwork::find($existingId)['piece_value'];
                }
                if (!$pieceValue) {
                    throw new InvalidArgumentException('Embed code is required.');
                }
                break;
            default:
                throw new InvalidArgumentException('Invalid piece type.');
        }

        return compact(
            'title', 'slug', 'year', 'description', 'catId',
            'thumbType', 'thumbValue', 'pieceType', 'pieceValue', 'sort'
        ) + [
            'category_id'      => $catId ?: null,
            'description'      => $desc,
            'thumbnail_type'   => $thumbType,
            'thumbnail_value'  => $thumbValue,
            'piece_type'       => $pieceType,
            'piece_value'      => $pieceValue,
            'sort_order'       => $sort,
        ];
    }

    // Resolves thumbnail_type + thumbnail_value from POST/FILES.
    // $existing is the current DB row (or null for create).
    // $prefix distinguishes file/link field names ('category', 'exhibit').
    private static function resolveThumbnail(?array $existing, string $prefix): array
    {
        $type = $_POST['thumbnail_type'] ?? 'link';
        if ($type === 'upload' && !empty($_FILES['thumbnail_upload']['name'])) {
            $value = upload_image($_FILES['thumbnail_upload'], $prefix . 's');
        } elseif ($type === 'link') {
            $value = trim($_POST['thumbnail_link'] ?? '') ?: null;
            if ($value === null) $type = null;
        } else {
            $type  = null;
            $value = null;
        }
        return [$type, $value];
    }

    private static function resolvePageData(?int $existingId): array
    {
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            throw new InvalidArgumentException('Title is required.');
        }

        $slugInput = trim($_POST['slug'] ?? '');
        $slug = Page::validateSlug($slugInput !== '' ? $slugInput : $title, $existingId ?? 0);
        $template = $_POST['template'] ?? 'standard';
        if (!in_array($template, ['standard', 'contact'], true)) {
            throw new InvalidArgumentException('Invalid page template.');
        }
        if ($template === 'contact' && $slug !== 'contact') {
            throw new InvalidArgumentException('The contact template must use the slug "contact".');
        }
        if ($template !== 'contact' && $slug === 'contact') {
            throw new InvalidArgumentException('The slug "contact" is reserved for the contact page template.');
        }

        $status = $_POST['status'] ?? 'published';
        if (!in_array($status, ['published', 'draft'], true)) {
            throw new InvalidArgumentException('Invalid page status.');
        }

        return [
            'title'            => $title,
            'slug'             => $slug,
            'status'           => $status,
            'template'         => $template,
            'nav_label'        => trim($_POST['nav_label'] ?? ''),
            'show_in_nav'      => !empty($_POST['show_in_nav']) ? 1 : 0,
            'meta_title'       => trim($_POST['meta_title'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
            'og_title'         => trim($_POST['og_title'] ?? ''),
            'og_description'   => trim($_POST['og_description'] ?? ''),
            'og_image'         => trim($_POST['og_image'] ?? ''),
            'sort_order'       => (int) ($_POST['sort_order'] ?? ($existingId ? (Page::find($existingId)['sort_order'] ?? 0) : 0)),
        ];
    }

    // ── Exhibits ──────────────────────────────────────────────────────────

    public static function exhibitsIndex(): void
    {
        admin_check();
        $exhibits = Exhibit::allWithArtworkCount();
        require dirname(__DIR__) . '/views/admin/exhibits/index.php';
    }

    public static function exhibitCreate(): void
    {
        admin_check();
        $exhibit    = null;
        $allArtworks = Artwork::all();
        $assigned   = [];
        $error      = null;
        require dirname(__DIR__) . '/views/admin/exhibits/form.php';
    }

    public static function exhibitStore(): void
    {
        admin_check();
        $name = trim($_POST['name'] ?? '');
        if (!$name) {
            $exhibit     = null;
            $allArtworks = Artwork::all();
            $assigned    = [];
            $error       = 'Name is required.';
            require dirname(__DIR__) . '/views/admin/exhibits/form.php';
            return;
        }
        $postedSlug = trim($_POST['slug'] ?? '');
        $slug       = $postedSlug ? slugify($postedSlug) : Exhibit::uniqueSlug($name);
        try {
            [$thumbType, $thumbValue] = self::resolveThumbnail(null, 'exhibit');
        } catch (\RuntimeException $e) {
            $exhibit     = null;
            $allArtworks = Artwork::all();
            $assigned    = [];
            $error       = $e->getMessage();
            require dirname(__DIR__) . '/views/admin/exhibits/form.php';
            return;
        }
        $id = Exhibit::create([
            'name'            => $name,
            'slug'            => $slug,
            'description'     => trim($_POST['description'] ?? ''),
            'thumbnail_type'  => $thumbType,
            'thumbnail_value' => $thumbValue,
            'sort_order'      => 0,
        ]);
        Exhibit::syncArtworks($id, array_map('intval', $_POST['artwork_ids'] ?? []));
        header('Location: /admin/exhibits');
        exit;
    }

    public static function exhibitEdit(string $id): void
    {
        admin_check();
        $exhibit = Exhibit::find((int) $id);
        if (!$exhibit) {
            header('Location: /admin/exhibits');
            exit;
        }
        $allArtworks = Artwork::all();
        $assigned    = Exhibit::artworkIds((int) $id);
        $error       = null;
        require dirname(__DIR__) . '/views/admin/exhibits/form.php';
    }

    public static function exhibitUpdate(string $id): void
    {
        admin_check();
        $existing = Exhibit::find((int) $id);
        $name     = trim($_POST['name'] ?? '');
        if (!$name) {
            $exhibit     = $existing;
            $allArtworks = Artwork::all();
            $assigned    = Exhibit::artworkIds((int) $id);
            $error       = 'Name is required.';
            require dirname(__DIR__) . '/views/admin/exhibits/form.php';
            return;
        }
        $postedSlug = trim($_POST['slug'] ?? '');
        $slug       = $postedSlug ? slugify($postedSlug) : Exhibit::uniqueSlug($name, (int) $id);
        try {
            [$thumbType, $thumbValue] = self::resolveThumbnail($existing, 'exhibit');
        } catch (\RuntimeException $e) {
            $exhibit     = $existing;
            $allArtworks = Artwork::all();
            $assigned    = Exhibit::artworkIds((int) $id);
            $error       = $e->getMessage();
            require dirname(__DIR__) . '/views/admin/exhibits/form.php';
            return;
        }
        Exhibit::update((int) $id, [
            'name'            => $name,
            'slug'            => $slug,
            'description'     => trim($_POST['description'] ?? ''),
            'thumbnail_type'  => $thumbType,
            'thumbnail_value' => $thumbValue,
            'sort_order'      => (int) ($existing['sort_order'] ?? 0),
        ]);
        Exhibit::syncArtworks((int) $id, array_map('intval', $_POST['artwork_ids'] ?? []));
        header('Location: /admin/exhibits');
        exit;
    }

    public static function exhibitDelete(string $id): void
    {
        admin_check();
        Exhibit::softDelete((int) $id);
        header('Location: /admin/exhibits');
        exit;
    }

    public static function exhibitReorder(): void
    {
        admin_check();
        $ids  = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));
        $stmt = db()->prepare('UPDATE exhibits SET sort_order = ? WHERE id = ?');
        foreach (array_values($ids) as $i => $id) {
            $stmt->execute([$i, $id]);
        }
        header('Content-Type: application/json');
        echo '{"ok":true}';
        exit;
    }

    // ── Media Library ─────────────────────────────────────────────────────

    public static function mediaIndex(): void
    {
        admin_check();
        $files = MediaFile::all();
        require dirname(__DIR__) . '/views/admin/media.php';
    }

    public static function mediaLibrary(): void
    {
        admin_check();
        header('Content-Type: application/json');
        echo json_encode(MediaFile::all());
        exit;
    }

    public static function mediaUpload(): void
    {
        admin_check();
        header('Content-Type: application/json');
        if (empty($_FILES['media_file']['name'])) {
            echo json_encode(['ok' => false, 'error' => 'No file provided.']);
            exit;
        }
        try {
            $url = upload_image($_FILES['media_file']);
            echo json_encode(['ok' => true, 'url' => $url]);
        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public static function mediaImport(): void
    {
        admin_check();
        header('Content-Type: application/json');
        $url = trim($_POST['url'] ?? '');
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid URL.']);
            exit;
        }
        $limit = 8 * 1024 * 1024;
        $ctx   = stream_context_create(['http' => ['timeout' => 20, 'follow_location' => true]]);
        $data  = @file_get_contents($url, false, $ctx, 0, $limit + 1);
        if ($data === false || $data === '') {
            echo json_encode(['ok' => false, 'error' => 'Could not fetch the URL.']);
            exit;
        }
        if (strlen($data) > $limit) {
            echo json_encode(['ok' => false, 'error' => 'Image exceeds 8 MB limit.']);
            exit;
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->buffer($data);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'], true)) {
            echo json_encode(['ok' => false, 'error' => 'URL does not point to a supported image type.']);
            exit;
        }
        $id = MediaFile::create($data, $mime);
        echo json_encode(['ok' => true, 'id' => $id, 'url' => "/image/$id"]);
        exit;
    }

    public static function mediaTrash(string $id): void
    {
        admin_check();
        MediaFile::softDelete((int) $id);
        header('Location: /admin/media');
        exit;
    }

    public static function mediaDestroy(string $id): void
    {
        admin_check();
        MediaFile::hardDelete((int) $id);
        header('Location: /admin/media');
        exit;
    }

    // ── Recycle Bin ───────────────────────────────────────────────────────

    public static function trashIndex(): void
    {
        admin_check();
        $tab        = $_GET['tab'] ?? 'artworks';
        $artworks   = Artwork::trashed();
        $categories = Category::trashed();
        $exhibits   = Exhibit::trashed();
        $mediaFiles = MediaFile::trashed();
        require dirname(__DIR__) . '/views/admin/trash.php';
    }

    public static function trashRestore(): void
    {
        admin_check();
        $type = $_POST['type'] ?? '';
        $id   = (int) ($_POST['id'] ?? 0);
        match ($type) {
            'artwork'  => Artwork::restore($id),
            'category' => Category::restore($id),
            'exhibit'  => Exhibit::restore($id),
            'media'    => MediaFile::restore($id),
            default    => null,
        };
        $tab = match ($type) {
            'artwork'  => 'artworks',
            'category' => 'categories',
            'exhibit'  => 'exhibits',
            default    => $type,
        };
        header("Location: /admin/trash?tab={$tab}");
        exit;
    }

    public static function trashPurge(): void
    {
        admin_check();
        $type = $_POST['type'] ?? '';
        $id   = (int) ($_POST['id'] ?? 0);
        match ($type) {
            'artwork'  => Artwork::hardDelete($id),
            'category' => Category::hardDelete($id),
            'exhibit'  => Exhibit::hardDelete($id),
            'media'    => MediaFile::hardDelete($id),
            default    => null,
        };
        $tab = match ($type) {
            'artwork'  => 'artworks',
            'category' => 'categories',
            'exhibit'  => 'exhibits',
            default    => $type,
        };
        header("Location: /admin/trash?tab={$tab}");
        exit;
    }

    public static function trashEmpty(): void
    {
        admin_check();
        $type = $_POST['type'] ?? '';
        switch ($type) {
            case 'artworks':
                foreach (Artwork::trashed() as $a) {
                    Artwork::hardDelete((int) $a['id']);
                }
                break;
            case 'categories':
                foreach (Category::trashed() as $c) {
                    Category::hardDelete((int) $c['id']);
                }
                break;
            case 'exhibits':
                foreach (Exhibit::trashed() as $e) {
                    Exhibit::hardDelete((int) $e['id']);
                }
                break;
            case 'media':
                foreach (MediaFile::trashed() as $f) {
                    MediaFile::hardDelete((int) $f['id']);
                }
                break;
        }
        header("Location: /admin/trash?tab={$type}");
        exit;
    }
}
