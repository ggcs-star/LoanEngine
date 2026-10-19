<?php

namespace App\Services\Education;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class EducationMediaService
{
    private const DISK = 'public';

    /**
     * Store an uploaded file.
     */
    public function store(
        UploadedFile $file,
        string $directory
    ): string {
        $path = $file->store($directory, self::DISK);

        if (! is_string($path) || $path === '') {
            throw new RuntimeException(
                'Unable to store the uploaded media.'
            );
        }

        return $path;
    }

    /**
     * Replace an existing file with a new upload.
     */
    public function replace(
        ?string $oldPath,
        ?UploadedFile $newFile,
        string $directory
    ): ?string {
        if ($newFile === null) {
            return $oldPath;
        }

        // Store the new file before removing the old one.
        $newPath = $this->store($newFile, $directory);

        try {
            $this->delete($oldPath);
        } catch (\Throwable $exception) {
            // Keep the newly uploaded file if cleanup fails.
            throw $exception;
        }

        return $newPath;
    }

    /**
     * Delete a file if it exists.
     */
    public function delete(?string $path): void
    {
        if ($path === null || trim($path) === '') {
            return;
        }

        $disk = Storage::disk(self::DISK);

        if ($disk->exists($path)) {
            $deleted = $disk->delete($path);

            if (! $deleted) {
                throw new RuntimeException(
                    'Unable to delete the stored media.'
                );
            }
        }
    }

    /**
     * Generate a public URL for a stored file.
     */
    public function url(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        return Storage::disk(self::DISK)->url($path);
    }

    /**
     * Check whether a stored file exists.
     */
    public function exists(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }

        return Storage::disk(self::DISK)->exists($path);
    }
}
