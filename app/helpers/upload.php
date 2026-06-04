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

    $blob = file_get_contents($file['tmp_name']);
    if ($blob === false) {
        throw new RuntimeException('Could not read uploaded file.');
    }

    // Ensure MySQL accepts large blob packets (default may be as low as 4MB)
    db()->exec('SET SESSION max_allowed_packet = 67108864');

    if (class_exists('MediaFile')) {
        $id = MediaFile::create(
            basename($file['name'] ?? 'upload'),
            trim($subfolder, '/'),
            $blob,
            $mime
        );
        return '/image/' . $id;
    }

    throw new RuntimeException('MediaFile class not available.');
}
