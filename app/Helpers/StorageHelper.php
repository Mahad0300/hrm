<?php

namespace App\Helpers;

/**
 * Filesystem paths for storage/uploads (outside public/).
 * DB and URLs keep the relative prefix "uploads/...".
 */
class StorageHelper
{
    public static function projectRoot(): string
    {
        return defined('ROOT_DIR') ? ROOT_DIR : dirname(__DIR__, 2);
    }

    public static function uploadsRoot(): string
    {
        return self::projectRoot() . '/storage/uploads';
    }

    /**
     * @param string $relativePath e.g. uploads/employees/profiles/file.png
     */
    public static function diskPath(string $relativePath): string
    {
        $relativePath = str_replace('\\', '/', ltrim($relativePath, '/'));
        if (str_starts_with($relativePath, 'uploads/')) {
            $relativePath = substr($relativePath, strlen('uploads/'));
        }

        return self::uploadsRoot() . '/' . $relativePath;
    }

    public static function ensureDirectory(string $relativeDir): void
    {
        $relativeDir = rtrim(str_replace('\\', '/', $relativeDir), '/') . '/';
        $diskDir = self::diskPath($relativeDir);
        if (!is_dir($diskDir)) {
            mkdir($diskDir, 0755, true);
        }
    }

    /**
     * Store an uploaded file under storage/uploads.
     *
     * @return string|null Public relative path stored in DB (uploads/...)
     */
    public static function storeUploadedFile(
        ?array $file,
        string $targetDir,
        string $newFileName
    ): ?string {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            return null;
        }

        // Server-Side MIME Type validation via Magic Bytes
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/octet-stream' // Fallback for doc files on certain systems
        ];

        if (function_exists('finfo_open') && !empty($file['tmp_name']) && file_exists($file['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if ($mime && !in_array(strtolower($mime), $allowedMimes, true)) {
                return null;
            }
        }

        if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
            return null;
        }

        $targetDir = rtrim(str_replace('\\', '/', $targetDir), '/') . '/';
        if (!str_starts_with($targetDir, 'uploads/')) {
            $targetDir = 'uploads/' . ltrim($targetDir, '/');
        }

        self::ensureDirectory($targetDir);

        $diskPath = self::diskPath($targetDir . $newFileName);
        if (!move_uploaded_file($file['tmp_name'], $diskPath)) {
            return null;
        }

        return $targetDir . $newFileName;
    }
}
