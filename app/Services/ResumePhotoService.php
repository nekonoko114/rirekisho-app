<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Modifiers\CoverModifier;

/**
 * Stores resume photos: crops/resizes to portrait 300x420 (quality 85)
 * under the public disk so they are served via the storage symlink.
 */
class ResumePhotoService
{
    private const MAX_DIMENSION = 4096;

    private const MAX_PIXELS = 16777216; // 4096 * 4096

    /**
     * Process and store an uploaded photo, returning its relative path.
     */
    public function store(UploadedFile $file): string
    {
        $realPath = $file->getRealPath();
        if (! $realPath || ! is_file($realPath)) {
            throw new \InvalidArgumentException('アップロードされた画像ファイルが見つかりません。');
        }

        // Guard against decompression bombs (OOM protection for CVE-2026-109618)
        $imageSize = @getimagesize($realPath);
        if ($imageSize === false) {
            throw new \InvalidArgumentException('有効な画像ファイルではありません。');
        }

        [$width, $height] = $imageSize;
        if (
            $width <= 0 ||
            $height <= 0 ||
            $width > self::MAX_DIMENSION ||
            $height > self::MAX_DIMENSION ||
            ($width * $height) > self::MAX_PIXELS
        ) {
            throw new \InvalidArgumentException('画像の解像度が上限を超えています。');
        }

        $driverClass = extension_loaded('imagick')
            ? Driver::class
            : \Intervention\Image\Drivers\Gd\Driver::class;

        $img = (new ImageManager($driverClass))->read($realPath);
        $img->modify(new CoverModifier(300, 420, 'center'));

        $photoDir = storage_path('app/public/photos');
        if (! is_dir($photoDir)) {
            @mkdir($photoDir, 0755, true);
        }

        $filename = 'photos/'.bin2hex(random_bytes(16)).'.jpg';
        $img->save(storage_path('app/public/'.$filename), 85);

        return $filename;
    }

    /**
     * Store a new photo and delete the old one (if any).
     */
    public function replace(UploadedFile $file, ?string $oldPath): string
    {
        $this->delete($oldPath);

        return $this->store($file);
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $normalized = ltrim($path, '/');
        if (! str_starts_with($normalized, 'photos/') || str_contains($normalized, '..')) {
            return;
        }

        Storage::disk('public')->delete($normalized);
    }
}
