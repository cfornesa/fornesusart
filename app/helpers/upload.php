<?php

declare(strict_types=1);

const ALLOWED_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
    'image/avif' => 'avif',
];

function upload_image(array $file, string $subfolder = ''): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds the server upload size limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form upload size limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'Upload blocked by server extension.',
        ];
        throw new RuntimeException($messages[$file['error']] ?? 'Upload error: ' . $file['error']);
    }

    // Validate by magic bytes, not extension or Content-Type header
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);

    if (!isset(ALLOWED_MIME[$mime])) {
        throw new RuntimeException('File type not permitted.');
    }

    $ext      = ALLOWED_MIME[$mime];
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $dir      = dirname(__DIR__, 2) . '/public/uploads';
    if ($subfolder) {
        $dir .= '/' . trim($subfolder, '/');
    }
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $dest = $dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not save uploaded file.');
    }

    $relative = '/uploads/' . ($subfolder ? trim($subfolder, '/') . '/' : '') . $filename;

    if (class_exists('MediaFile')) {
        MediaFile::create($relative, trim($subfolder, '/'));
    }

    return $relative;
}
