<?php

namespace App\Modules\Property\Services;

use App\Support\PublicWebRoot;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PropertyMediaStorage
{
    public const LEGACY_DISK = 'property_media';

    private const PUBLIC_DISK = 'public';

    private const IMAGE_DIRECTORY = 'properties/images';

    private const DOCUMENT_DIRECTORY = 'properties/documents';

    public function diskName(): string
    {
        return (string) config('filesystems.property_media_disk', self::LEGACY_DISK);
    }

    public function storeImage(UploadedFile $file): string
    {
        $this->ensureDirectoriesExist();

        return $this->storeUploadedFile($file, self::IMAGE_DIRECTORY);
    }

    public function storeDocument(UploadedFile $file): string
    {
        $this->ensureDirectoriesExist();

        return $this->storeUploadedFile($file, self::DOCUMENT_DIRECTORY);
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $this->disk()->delete($path);

        if ($this->diskName() !== self::LEGACY_DISK) {
            Storage::disk(self::LEGACY_DISK)->delete($path);
        }

        Storage::disk(self::PUBLIC_DISK)->delete($path);

        $legacyPublicPath = $this->absolutePathForRoot(public_path('storage'), $path);

        if (is_file($legacyPublicPath)) {
            unlink($legacyPublicPath);
        }

        $webRootPath = $this->absolutePathForRoot(PublicWebRoot::storagePath(), $path);

        if (is_file($webRootPath) && $webRootPath !== $legacyPublicPath) {
            unlink($webRootPath);
        }
    }

    public function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        // Prefer legacy local files so existing GoDaddy media keep working.
        if ($this->legacyLocalExists($path)) {
            $this->ensureAccessibleFromPublicWebRoot($path);

            return Storage::disk(self::LEGACY_DISK)->url($path);
        }

        if ($this->usesCloudDisk()) {
            return $this->cloudUrl($path);
        }

        return $this->disk()->url($path);
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

    public function usesCloudDisk(): bool
    {
        $driver = config('filesystems.disks.'.$this->diskName().'.driver');

        return $driver === 's3';
    }

    private function cloudUrl(string $path): string
    {
        $minutes = (int) config('filesystems.minio_signed_url_minutes', 60 * 24);

        try {
            return $this->disk()->temporaryUrl($path, now()->addMinutes(max(1, $minutes)));
        } catch (\Throwable) {
            // Fall back to configured public URL if temporary URLs are unsupported.
            return $this->disk()->url($path);
        }
    }

    private function disk(): Filesystem
    {
        return Storage::disk($this->diskName());
    }

    private function storeUploadedFile(UploadedFile $file, string $directory): string
    {
        if ($this->usesCloudDisk()) {
            return $file->storePublicly($directory, $this->diskName());
        }

        return $file->store($directory, $this->diskName());
    }

    private function ensureDirectoriesExist(): void
    {
        if ($this->usesCloudDisk()) {
            return;
        }

        $this->disk()->makeDirectory(self::IMAGE_DIRECTORY);
        $this->disk()->makeDirectory(self::DOCUMENT_DIRECTORY);
    }

    private function legacyLocalExists(string $path): bool
    {
        foreach ($this->candidateAbsolutePaths($path) as $absolutePath) {
            if (is_file($absolutePath)) {
                return true;
            }
        }

        return Storage::disk(self::LEGACY_DISK)->exists($path);
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
