<?php

namespace App\Modules\Advertisement\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdvertisementMediaStorage
{
    private const LEGACY_DISK = 'public';

    private const CLOUD_DISK = 'minio';

    private const DIRECTORY = 'advertisements';

    public function diskName(): string
    {
        $configured = (string) config('filesystems.property_media_disk', self::LEGACY_DISK);

        return $configured === self::CLOUD_DISK ? self::CLOUD_DISK : self::LEGACY_DISK;
    }

    public function store(UploadedFile $file): string
    {
        return $this->storeIn(self::DIRECTORY, $file);
    }

    public function storeImage(UploadedFile $file): string
    {
        return $this->storeIn(self::DIRECTORY.'/images', $file);
    }

    public function storeDocument(UploadedFile $file): string
    {
        return $this->storeIn(self::DIRECTORY.'/documents', $file);
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
    }

    public function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (Storage::disk(self::LEGACY_DISK)->exists($path)) {
            return Storage::disk(self::LEGACY_DISK)->url($path);
        }

        if ($this->usesCloudDisk()) {
            return $this->cloudUrl($path);
        }

        return Storage::disk(self::LEGACY_DISK)->url($path);
    }

    public function usesCloudDisk(): bool
    {
        return $this->diskName() === self::CLOUD_DISK;
    }

    private function storeIn(string $directory, UploadedFile $file): string
    {
        if ($this->usesCloudDisk()) {
            return $file->storePublicly($directory, $this->diskName());
        }

        return $file->store($directory, self::LEGACY_DISK);
    }

    private function disk(): Filesystem
    {
        return Storage::disk($this->diskName());
    }

    private function cloudUrl(string $path): string
    {
        $minutes = (int) config('filesystems.minio_signed_url_minutes', 60 * 24);

        try {
            return $this->disk()->temporaryUrl($path, now()->addMinutes(max(1, $minutes)));
        } catch (\Throwable) {
            return $this->disk()->url($path);
        }
    }
}
