<?php

namespace App\Support;

use App\Models\MediaAsset;

final class ResolvedEditorialImage
{
    public function __construct(
        public readonly ?MediaAsset $media,
        public readonly ?string $url,
        public readonly string $alt,
        public readonly string $sourceType,
        public readonly ?string $sourceEntity = null,
    ) {
    }

    public function isMedia(): bool
    {
        return $this->media !== null;
    }

    public function isFallback(): bool
    {
        return $this->sourceType === 'fallback';
    }
}
