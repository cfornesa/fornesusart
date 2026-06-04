<?php

declare(strict_types=1);

class Artwork
{
    public static function allSorted(): array
    {
        return db()->query(
            'SELECT a.*, c.name AS category_name, c.slug AS category_slug
             FROM artworks a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.deleted_at IS NULL
             ORDER BY a.sort_order ASC, a.id ASC'
        )->fetchAll();
    }

    public static function allGroupedByCategory(): array
    {
        $categories = db()->query(
            'SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY sort_order ASC, id ASC'
        )->fetchAll();

        $uncategorized = db()->query(
            'SELECT * FROM artworks WHERE category_id IS NULL AND deleted_at IS NULL ORDER BY sort_order ASC, id ASC'
        )->fetchAll();

        $result = [];

        foreach ($categories as $cat) {
            $stmt = db()->prepare(
                'SELECT * FROM artworks WHERE category_id = ? AND deleted_at IS NULL ORDER BY sort_order ASC, id ASC'
            );
            $stmt->execute([$cat['id']]);
            $works = $stmt->fetchAll();
            if ($works) {
                $result[] = ['category' => $cat, 'artworks' => $works];
            }
        }

        if ($uncategorized) {
            $result[] = ['category' => null, 'artworks' => $uncategorized];
        }

        return $result;
    }

    public static function all(): array
    {
        return db()->query(
            'SELECT a.*, c.name AS category_name, c.slug AS category_slug
             FROM artworks a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.deleted_at IS NULL
             ORDER BY a.sort_order ASC, a.id ASC'
        )->fetchAll();
    }

    public static function find(int $id): array|false
    {
        $stmt = db()->prepare(
            'SELECT a.*, c.name AS category_name, c.slug AS category_slug
             FROM artworks a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.id = ? AND a.deleted_at IS NULL'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findBySlug(string $slug): array|false
    {
        $stmt = db()->prepare(
            'SELECT a.*, c.name AS category_name, c.slug AS category_slug
             FROM artworks a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.slug = ? AND a.deleted_at IS NULL'
        );
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public static function trashed(): array
    {
        return db()->query(
            'SELECT a.*, c.name AS category_name
             FROM artworks a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.deleted_at IS NOT NULL
             ORDER BY a.deleted_at DESC'
        )->fetchAll();
    }

    public static function trashedCount(): int
    {
        return (int) db()->query(
            'SELECT COUNT(*) FROM artworks WHERE deleted_at IS NOT NULL'
        )->fetchColumn();
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO artworks
                (category_id, title, slug, year, description,
                 thumbnail_type, thumbnail_value,
                 piece_type, piece_value, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['category_id'] ?: null,
            $data['title'],
            $data['slug'],
            $data['year'] ?: null,
            $data['description'] ?: null,
            $data['thumbnail_type'] ?: null,
            $data['thumbnail_value'] ?: null,
            $data['piece_type'],
            $data['piece_value'],
            $data['sort_order'] ?? 0,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = db()->prepare(
            'UPDATE artworks SET
                category_id = ?, title = ?, slug = ?, year = ?, description = ?,
                thumbnail_type = ?, thumbnail_value = ?,
                piece_type = ?, piece_value = ?, sort_order = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $data['category_id'] ?: null,
            $data['title'],
            $data['slug'],
            $data['year'] ?: null,
            $data['description'] ?: null,
            $data['thumbnail_type'] ?: null,
            $data['thumbnail_value'] ?: null,
            $data['piece_type'],
            $data['piece_value'],
            $data['sort_order'] ?? 0,
            $id,
        ]);
    }

    public static function softDelete(int $id): void
    {
        $stmt = db()->prepare('UPDATE artworks SET deleted_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function hardDelete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM artworks WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function restore(int $id): void
    {
        $stmt = db()->prepare('UPDATE artworks SET deleted_at = NULL WHERE id = ?');
        $stmt->execute([$id]);
    }
}
