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

    // ── Bio ───────────────────────────────────────────────────────────────

    public static function bioIndex(): void
    {
        admin_check();
        $sections = BioSection::all();
        require dirname(__DIR__) . '/views/admin/bio/index.php';
    }

    public static function bioCreate(): void
    {
        admin_check();
        $section = null;
        $error   = null;
        require dirname(__DIR__) . '/views/admin/bio/form.php';
    }

    public static function bioStore(): void
    {
        admin_check();
        $heading = trim($_POST['heading'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if (!$content) {
            $section = null;
            $error   = 'Content is required.';
            require dirname(__DIR__) . '/views/admin/bio/form.php';
            return;
        }
        BioSection::create($heading, $content);
        header('Location: /admin/bio');
        exit;
    }

    public static function bioEdit(string $id): void
    {
        admin_check();
        $section = BioSection::find((int) $id);
        if (!$section) {
            header('Location: /admin/bio');
            exit;
        }
        $error = null;
        require dirname(__DIR__) . '/views/admin/bio/form.php';
    }

    public static function bioUpdate(string $id): void
    {
        admin_check();
        $heading = trim($_POST['heading'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if (!$content) {
            $section = BioSection::find((int) $id);
            $error   = 'Content is required.';
            require dirname(__DIR__) . '/views/admin/bio/form.php';
            return;
        }
        BioSection::update((int) $id, $heading, $content, 0);
        header('Location: /admin/bio');
        exit;
    }

    public static function bioDelete(string $id): void
    {
        admin_check();
        BioSection::delete((int) $id);
        header('Location: /admin/bio');
        exit;
    }

    public static function bioReorder(): void
    {
        admin_check();
        $ids  = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));
        $stmt = db()->prepare('UPDATE bio_sections SET sort_order = ? WHERE id = ?');
        foreach (array_values($ids) as $i => $id) {
            $stmt->execute([$i, $id]);
        }
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

        // Thumbnail
        $thumbType = $_POST['thumbnail_type'] ?? 'link';
        if ($thumbType === 'upload' && !empty($_FILES['thumbnail_upload']['name'])) {
            $thumbValue = upload_image($_FILES['thumbnail_upload'], 'thumbnails');
        } elseif ($thumbType === 'link' && !empty($_POST['thumbnail_link'])) {
            $thumbValue = trim($_POST['thumbnail_link']);
        } elseif ($existingId) {
            $existing   = Artwork::find($existingId);
            $thumbType  = $existing['thumbnail_type'];
            $thumbValue = $existing['thumbnail_value'];
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
        } elseif ($type === 'link' && !empty($_POST['thumbnail_link'])) {
            $value = trim($_POST['thumbnail_link']);
        } elseif ($existing) {
            $type  = $existing['thumbnail_type'] ?? null;
            $value = $existing['thumbnail_value'] ?? null;
        } else {
            $type  = null;
            $value = null;
        }
        return [$type, $value];
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
