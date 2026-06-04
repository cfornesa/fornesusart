<?php

declare(strict_types=1);

class MediaFile
{
    public static function create(string $path, string $subfolder, string $data, string $mimeType): int
    {
        $pdo  = db();
        $stmt = $pdo->prepare(
            'INSERT INTO media_files (path, subfolder, data, mime_type) VALUES (?, ?, ?, ?)'
        );
        $stmt->bindValue(1, $path);
        $stmt->bindValue(2, $subfolder);
        $stmt->bindParam(3, $data, PDO::PARAM_LOB);
        $stmt->bindValue(4, $mimeType);
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    public static function all(): array
    {
        return db()->query(
            'SELECT id, path, subfolder, mime_type, deleted_at, created_at FROM media_files WHERE deleted_at IS NULL ORDER BY created_at DESC'
        )->fetchAll();
    }

    public static function trashed(): array
    {
        return db()->query(
            'SELECT id, path, subfolder, mime_type, deleted_at, created_at FROM media_files WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC'
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
        $stmt = db()->prepare(
            'SELECT id, path, subfolder, mime_type, deleted_at, created_at FROM media_files WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getData(int $id): array|false
    {
        $stmt = db()->prepare(
            'SELECT id, mime_type, deleted_at, data FROM media_files WHERE id = ?'
        );
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
        if (self::find($id)) {
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
