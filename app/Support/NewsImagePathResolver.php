<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsImagePathResolver
{
    public static function toPublicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $trimmed = trim($path);

        if ($trimmed === '' || str_starts_with($trimmed, '/tmp/')) {
            return null;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            return $trimmed;
        }

        foreach (self::storageCandidates($trimmed) as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return Storage::disk('public')->url($candidate);
            }
        }

        return null;
    }

    public static function toLegacyStoredPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $trimmed = trim($path);

        if ($trimmed === '' || str_starts_with($trimmed, '/tmp/')) {
            return null;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            $parsedPath = parse_url($trimmed, PHP_URL_PATH);
            $trimmed = is_string($parsedPath) ? ltrim($parsedPath, '/') : $trimmed;
        }

        $trimmed = preg_replace('#^storage/#', '', $trimmed);

        if (Str::startsWith($trimmed, 'news/')) {
            return $trimmed;
        }

        return basename($trimmed);
    }

    public static function storageCandidates(?string $path): array
    {
        $normalized = self::toLegacyStoredPath($path);

        if (!$normalized) {
            return [];
        }

        $candidates = [$normalized];

        if (!Str::startsWith($normalized, 'news/')) {
            $candidates[] = 'noticias/' . $normalized;
            $candidates[] = 'news/' . $normalized;
        }

        return array_values(array_unique($candidates));
    }
}
