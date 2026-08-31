<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

final class ImageSourceMetadata
{
    public static function from(mixed $source): array
    {
        if (is_string($source) && filter_var($source, FILTER_VALIDATE_URL)) {
            return [
                'source_url' => $source,
                'source_type' => 'external',
                'rights_status' => 'unknown',
            ];
        }

        if ($source instanceof UploadedFile) {
            return [
                'source_type' => 'upload',
                'rights_status' => 'unknown',
            ];
        }

        return [];
    }
}
