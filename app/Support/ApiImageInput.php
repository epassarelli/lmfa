<?php

namespace App\Support;

use Illuminate\Http\Request;

final class ApiImageInput
{
    /**
     * Extract the processable image source from an API request while keeping
     * legacy relative featured_image_path values as model data.
     */
    public static function extract(Request $request, array &$payload, string $fileField): mixed
    {
        if ($file = $request->file($fileField)) {
            unset($payload['featured_image_url']);

            return $file;
        }

        $explicitUrl = $payload['featured_image_url'] ?? null;
        unset($payload['featured_image_url']);

        if (is_string($explicitUrl) && filter_var($explicitUrl, FILTER_VALIDATE_URL)) {
            return $explicitUrl;
        }

        $legacyPath = $payload['featured_image_path'] ?? null;

        if (is_string($legacyPath) && filter_var($legacyPath, FILTER_VALIDATE_URL)) {
            unset($payload['featured_image_path']);

            return $legacyPath;
        }

        return null;
    }
}
