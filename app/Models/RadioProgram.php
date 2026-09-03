<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RadioProgram extends Model
{
    use HasFactory, HasPublicationState;

    protected $fillable = ['radio_signal_id', 'title', 'slug', 'excerpt', 'body', 'is_folklore', 'platform', 'listening_url', 'source_urls', 'verification_status', 'last_verified_at', 'verified_by_user_id', 'verification_method', 'editorial_status', 'published_at', 'created_by', 'seo_title', 'meta_description', 'visits'];

    protected $casts = ['is_folklore' => 'boolean', 'source_urls' => 'array', 'last_verified_at' => 'datetime', 'published_at' => 'datetime', 'visits' => 'integer'];

    protected static function booted(): void
    {
        static::saving(function (self $program) {
            if (blank($program->slug) && filled($program->title)) {
                $program->slug = Str::slug($program->title);
            }
        });
    }

    public function signal()
    {
        return $this->belongsTo(RadioSignal::class, 'radio_signal_id');
    }

    public function slots()
    {
        return $this->hasMany(RadioProgramSlot::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
}
