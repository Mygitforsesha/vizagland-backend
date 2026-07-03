<?php

namespace App\Modules\Property\Services;

use App\Support\PublicWebRoot;
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

        $legacyPublicPath = $this->absolutePathForRoot(public_path('storage'), $path);

        if (is_file($legacyPublicPath)) {
            unlink($legacyPublicPath);
        }
    }

    public function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $this->ensureAccessibleFromPublicWebRoot($path);

        return Storage::disk(self::DISK)->url($path);
    }

    public function resolveAbsolutePath(string $path): ?string
    {
        $this->ensureAccessibleFromPublicWebRoot($path);

        foreach ($this->candidateAbsolutePaths($path) as $absolutePath) {
            if (is_file($absolutePath)) {
                return $absolutePath;
            }
        }

        return null;
    }

    public function publicRoot(): string
    {
        return PublicWebRoot::storagePath();
    }

    private function ensureDirectoriesExist(): void
    {
        Storage::disk(self::DISK)->makeDirectory(self::IMAGE_DIRECTORY);
        Storage::disk(self::DISK)->makeDirectory(self::DOCUMENT_DIRECTORY);
    }

    private function ensureAccessibleFromPublicWebRoot(string $path): void
    {
        $target = $this->absolutePathForRoot(PublicWebRoot::storagePath(), $path);

        if (is_file($target)) {
            return;
        }

        foreach ($this->candidateAbsolutePaths($path) as $source) {
            if (! is_file($source) || $source === $target) {
                continue;
            }

            $directory = dirname($target);

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            copy($source, $target);

            return;
        }
    }

    /**
     * @return list<string>
     */
    private function candidateAbsolutePaths(string $path): array
    {
        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        return array_values(array_unique([
            $this->absolutePathForRoot(PublicWebRoot::storagePath(), $normalizedPath),
            $this->absolutePathForRoot(public_path('storage'), $normalizedPath),
            $this->absolutePathForRoot(storage_path('app/public'), $normalizedPath),
        ]));
    }

    private function absolutePathForRoot(string $root, string $path): string
    {
        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        return rtrim($root, '/\\').DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);
    }
}
