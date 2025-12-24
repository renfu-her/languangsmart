<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload and convert image to webp format
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $oldPath
     * @return string
     */
    public function uploadImage(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        // Delete old image if exists
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Generate UUID filename
        $uuid = (string) Str::uuid();
        $filename = $uuid . '.webp';
        $path = $directory . '/' . $filename;

        // Create directory if not exists
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Read and convert image
        $image = $this->imageManager->read($file->getRealPath());
        
        // Convert to webp and save
        $image->toWebp(90)->save(storage_path('app/public/' . $path));

        return $path;
    }

    /**
     * Delete image
     *
     * @param string|null $path
     * @return bool
     */
    public function deleteImage(?string $path): bool
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }
}

