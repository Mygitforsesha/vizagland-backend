<?php

namespace App\Support;

final class PublicWebRoot
{
    public static function path(): string
    {
        $configured = config('filesystems.public_web_root');

        if (is_string($configured) && $configured !== '') {
            return rtrim($configured, '/\\');
        }

        if (! app()->runningInConsole()) {
            $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';

            if (is_string($documentRoot) && $documentRoot !== '') {
                return rtrim($documentRoot, '/\\');
            }
        }

        return public_path();
    }

    public static function storagePath(): string
    {
        return self::path().DIRECTORY_SEPARATOR.'storage';
    }
}
