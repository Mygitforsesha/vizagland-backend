<?php

namespace App\Modules\Property\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PropertyMediaStorage
{
    public const DISK = 'property_media';

    private const LEGACY_DISK = 'public';

    private const IMAGE_DIRECTORY = 'properties/images';

    private const DOCUMENT_DIRECTORY = 'properties/documents';

    public function storeImage(UploadedFile $file): string
    {
        $this->ensureDirectoriesExist();

        return $file->store(self::IMAGE_DIRECTORY, self::DISK);
    }

    public function storeDocument(UploadedFile $file): string
    {
        $this->ensureDirectoriesExist();

        return $file->store(self::DOCUMENT_DIRECTORY, self::DISK);
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk(self::DISK)->delete($path);
        Storage::disk(self::LEGACY_DISK)->delete($path);
    }

    public function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $this->ensureAccessibleFromPublicWebRoot($path);

        return Storage::disk(self::DISK)->url($path);
    }

    public function publicRoot(): string
    {
        return Storage::disk(self::DISK)->path('');
    }

    private function ensureDirectoriesExist(): void
    {
        Storage::disk(self::DISK)->makeDirectory(self::IMAGE_DIRECTORY);
        Storage::disk(self::DISK)->makeDirectory(self::DOCUMENT_DIRECTORY);
    }

    private function ensureAccessibleFromPublicWebRoot(string $path): void
    {
        if (Storage::disk(self::DISK)->exists($path)) {
            return;
        }

        if (! Storage::disk(self::LEGACY_DISK)->exists($path)) {
            return;
        }

        $directory = dirname($path);

        if ($directory !== '.' && $directory !== '') {
            Storage::disk(self::DISK)->makeDirectory($directory);
        }

        Storage::disk(self::DISK)->put(
            $path,
            Storage::disk(self::LEGACY_DISK)->get($path),
        );
    }
}
