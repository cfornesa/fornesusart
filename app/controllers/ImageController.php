<?php

declare(strict_types=1);

class ImageController
{
    public static function serve(string $id): void
    {
        $id = (int) $id;
        if ($id <= 0) {
            http_response_code(404);
            exit;
        }

        $row = MediaFile::getData($id);

        if (!$row || $row['deleted_at'] !== null || $row['data'] === null) {
            http_response_code(404);
            exit;
        }

        $mime = $row['mime_type'] ?? 'application/octet-stream';
        $etag = '"' . md5((string) $row['id'] . $mime) . '"';

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
            http_response_code(304);
            exit;
        }

        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('ETag: ' . $etag);
        header('Content-Length: ' . mb_strlen($row['data'], '8bit'));
        echo $row['data'];
        exit;
    }
}
