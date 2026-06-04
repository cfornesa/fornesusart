<?php

declare(strict_types=1);

class MediaFile
{
    public static function create(string $path, string $subfolder): int
    {
        $stmt = db()->prepare(
            'INSERT INTO media_files (path, subfolder) VALUES (?, ?)'
        );
        $stmt->execute([$path, $subfolder]);
        return (int) db()->lastInsertId();
    }

    public static function all(): array
    {
        return db()->query(
            'SELECT * FROM media_files WHERE deleted_at IS NULL ORDER BY created_at DESC'
        )->fetchAll();
    }

    public static function trashed(): array
    {
        return db()->query(
            'SELECT * FROM media_files WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC'
        )->fetchAll();
    }

    public static function trashedCount(): int
    {
        return (int) db()->query(
            'SELECT COUNT(*) FROM media_files WHERE deleted_at IS NOT NULL'
        )->fetchColumn();
    }

    public static function find(int $id): array|false
    {
        $stmt = db()->prepare('SELECT * FROM media_files WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function softDelete(int $id): void
    {
        $stmt = db()->prepare('UPDATE media_files SET deleted_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function hardDelete(int $id): void
    {
        $file = self::find($id);
        if ($file) {
            $physical = dirname(__DIR__, 2) . '/public' . $file['path'];
            if (is_file($physical)) {
                @unlink($physical);
            }
            $stmt = db()->prepare('DELETE FROM media_files WHERE id = ?');
            $stmt->execute([$id]);
        }
    }

    public static function restore(int $id): void
    {
        $stmt = db()->prepare('UPDATE media_files SET deleted_at = NULL WHERE id = ?');
        $stmt->execute([$id]);
    }
}
