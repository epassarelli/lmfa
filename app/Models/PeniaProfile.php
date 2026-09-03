<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationState;
use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PeniaProfile extends Model
{
    use HasFactory;
    use HasMedia;
    use HasPublicationState;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'province_id', 'locality_id',
        'city', 'address', 'latitude', 'longitude', 'venue_type', 'opening_hours', 'phone',
        'email', 'website', 'reservation_url', 'capacity', 'accessibility_notes',
        'regular_events_summary', 'admission_notes', 'source_urls', 'verification_status',
        'last_verified_at', 'verified_by_user_id', 'verification_method', 'editorial_status',
        'published_at', 'created_by', 'featured_image_id', 'featured_image_path', 'image_alt',
        'seo_title', 'meta_description', 'visits',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'source_urls' => 'array',
        'last_verified_at' => 'datetime',
        'published_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'capacity' => 'integer',
        'visits' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $profile) {
            if (blank($profile->slug) && filled($profile->title)) {
                $profile->slug = Str::slug($profile->title);
            }
        });
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'province_id');
    }

    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'penia_profile_event')->withTimestamps();
    }

    public function scopeVerificationCurrent(Builder $query): Builder
    {
        return $query
            ->where('verification_status', 'verified')
            ->whereNotNull('verified_by_user_id')
            ->whereNotNull('verification_method')
            ->whereNotNull('last_verified_at')
            ->whereBetween('last_verified_at', [now()->subDays(90), now()]);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->publishedVisible()->verificationCurrent();
    }

    public function hasCurrentVerification(): bool
    {
        return $this->verification_status === 'verified'
            && filled($this->verified_by_user_id)
            && filled($this->verification_method)
            && $this->last_verified_at?->betweenIncluded(now()->subDays(90), now());
    }

    public function getUrl(): string
    {
        return route('penia-profiles.show', $this->slug);
    }
}
