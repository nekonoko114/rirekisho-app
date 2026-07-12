<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Modifiers\CoverModifier;

/**
 * Stores resume photos: crops/resizes to portrait 300x420 (quality 85)
 * under the public disk so they are served via the storage symlink.
 */
class ResumePhotoService
{
    /**
     * Process and store an uploaded photo, returning its relative path.
     */
    public function store(UploadedFile $file): string
    {
        $driverClass = extension_loaded('imagick')
            ? \Intervention\Image\Drivers\Imagick\Driver::class
            : \Intervention\Image\Drivers\Gd\Driver::class;

        $img = (new ImageManager($driverClass))->read($file->getRealPath());
        $img->modify(new CoverModifier(300, 420, 'center'));

        $filename = 'photos/'.uniqid('', true).'.jpg';
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
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
