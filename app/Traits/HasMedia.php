<?php

namespace App\Traits;

use App\Models\MediaAsset;
use App\Models\Image;

trait HasMedia
{
    /**
     * Get all of the model's media assets.
     */
    public function media()
    {
        return $this->morphMany(MediaAsset::class, 'imageable');
    }

    /**
     * Compatibility: Get all of the model's images (using old name).
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Helper to get featured media.
     */
    public function featuredMedia()
    {
        return $this->media()->where('group', 'featured')->first();
    }
}
