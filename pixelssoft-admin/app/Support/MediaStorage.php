<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MediaStorage
{
    public const UPLOAD_DIR = 'uploads';

    public static function store(UploadedFile $file): string
    {
        $directory = public_path(self::UPLOAD_DIR);
        File::ensureDirectoryExists($directory);

        $name = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $name);

        return '/' . self::UPLOAD_DIR . '/' . $name;
    }

    public static function delete(?string $path): void
    {
        $fullPath = self::resolveFilesystemPath($path);
        if ($fullPath && File::isFile($fullPath)) {
            File::delete($fullPath);
        }
    }

    public static function resolveFilesystemPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = parse_url($path, PHP_URL_PATH) ?: $path;

        if (str_starts_with($path, '/storage/')) {
            return storage_path('app/public/' . ltrim(substr($path, strlen('/storage/')), '/'));
        }

        if (str_starts_with($path, '/' . self::UPLOAD_DIR . '/')) {
            return public_path(ltrim($path, '/'));
        }

        if (str_starts_with($path, self::UPLOAD_DIR . '/')) {
            return public_path($path);
        }

        return null;
    }
}
