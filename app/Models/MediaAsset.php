<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    use HasFactory;

    protected $table = 'media_assets';

    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'disk',
        'original_name',
        'original_path',
        'original_width',
        'original_height',
        'mime',
        'size',
        'alt',
        'caption',
        'group',
        'sort_order',
        'profile',
        'created_by',
        'variants_json',
    ];

    protected $casts = [
        'variants_json' => 'array',
        'size' => 'integer',
        'original_width' => 'integer',
        'original_height' => 'integer',
        'sort_order' => 'integer',
    ];

    public function imageable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias: $asset->path → original_path (columna real en BD).
     */
    public function getPathAttribute(): ?string
    {
        return $this->attributes['original_path'] ?? null;
    }

    public function setPathAttribute($value): void
    {
        $this->attributes['original_path'] = $value;
    }
}
