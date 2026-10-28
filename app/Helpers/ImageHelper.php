<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageHelper
{
    /**
     * Store a new image with a unique, readable filename.
     *
     * Example:
     * video-1.png
     * video-2.png
     * web-development-1.jpg
     */
    public static function store(
        UploadedFile $file,
        string $directory,
        string $name
    ): string {
        $disk = Storage::disk('public');

        $extension = strtolower($file->extension() ?: 'bin');

        $baseName = Str::slug($name);

        if ($baseName === '') {
            $baseName = 'image';
        }

        $directory = trim($directory, '/');

        $number = 1;

        do {
            $filename = "{$baseName}-{$number}.{$extension}";
            $path = "{$directory}/{$filename}";
            $number++;
        } while ($disk->exists($path));

        $storedPath = $file->storeAs(
            $directory,
            $filename,
            'public'
        );

        if (! is_string($storedPath) || $storedPath === '') {
            throw new RuntimeException(
                'Unable to store the uploaded image.'
            );
        }

        return $storedPath;
    }
}
