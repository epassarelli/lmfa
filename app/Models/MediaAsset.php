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
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
        'caption',
        'group',
        'sort_order',
        'profile', // Old field
        'created_by',
        'variants_json',
        // Compatibility
        'original_path',
        'mime',
        'alt',
    ];

    protected $casts = [
        'variants_json' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
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

    // path is an alias for the real DB column original_path
    public function getPathAttribute(): ?string
    {
        return $this->attributes['original_path'] ?? null;
    }

    public function setPathAttribute($value): void
    {
        $this->attributes['original_path'] = $value;
    }

    public function getMimeAttribute()
    {
        return $this->mime_type;
    }

    public function setMimeAttribute($value)
    {
        $this->mime_type = $value;
    }

    public function getAltAttribute()
    {
        return $this->alt_text;
    }

    public function setAltAttribute($value)
    {
        $this->alt_text = $value;
    }
}
