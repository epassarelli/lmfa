<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationState;
use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RadioSignal extends Model
{
    use HasFactory, HasMedia, HasPublicationState;

    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'editorial_focus', 'transmission_modes', 'province_id', 'locality_id', 'city', 'address', 'latitude', 'longitude', 'coverage_scope', 'coverage_notes', 'phone', 'email', 'website', 'source_urls', 'verification_status', 'last_verified_at', 'verified_by_user_id', 'verification_method', 'editorial_status', 'published_at', 'created_by', 'featured_image_path', 'image_alt', 'seo_title', 'meta_description', 'visits'];

    protected $casts = ['transmission_modes' => 'array', 'source_urls' => 'array', 'last_verified_at' => 'datetime', 'published_at' => 'datetime', 'latitude' => 'decimal:8', 'longitude' => 'decimal:8', 'visits' => 'integer'];

    protected static function booted(): void
    {
        static::saving(function (self $signal) {
            if (blank($signal->slug) && filled($signal->title)) {
                $signal->slug = Str::slug($signal->title);
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

    public function listeningChannels()
    {
        return $this->hasMany(RadioListeningChannel::class);
    }

    public function programs()
    {
        return $this->hasMany(RadioProgram::class);
    }

    public function scopeVerificationCurrent(Builder $query): Builder
    {
        return $query->where('verification_status', 'verified')->whereNotNull('verified_by_user_id')->whereNotNull('verification_method')->whereBetween('last_verified_at', [now()->subDays(90), now()]);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->publishedVisible()->verificationCurrent();
    }

    public function hasCurrentVerification(): bool
    {
        return $this->verification_status === 'verified' && filled($this->verified_by_user_id) && filled($this->verification_method) && $this->last_verified_at?->betweenIncluded(now()->subDays(90), now());
    }

    public function getUrl(): string
    {
        return route('radios.show', $this->slug);
    }
}
