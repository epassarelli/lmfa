<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\HasMedia;

class Organization extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'legal_name',
        'bio_short',
        'bio_long',
        'website',
        'email',
        'phone',
        'province_id',
        'city',
        'address',
        'logo_media_id',
        'cover_media_id',
        'is_verified',
        'instagram',
        'facebook',
        'contact_stage',
        'last_contacted_at',
        'contact_notes',
        'contact_consent_at',
        'contact_consent_purpose',
        'unsubscribed_at',
        'source',
        'verified_at',
        'verification_method',
        'verified_by',
        'status',
        'created_by',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'contact_consent_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_contacted_at' => 'datetime',
    ];

    public function getInstagramUrlAttribute(): ?string
    {
        return $this->socialUrl($this->instagram, 'https://www.instagram.com/');
    }

    public function getFacebookUrlAttribute(): ?string
    {
        return $this->socialUrl($this->facebook, 'https://www.facebook.com/');
    }

    private function socialUrl(?string $value, string $base): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return $base.ltrim($value, '@/');
    }

    /**
     * Organizaciones con consentimiento de contacto vigente y sin baja.
     */
    public function scopeContactable($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('contact_consent_at')
            ->whereNull('unsubscribed_at');
    }

    public function entityLinks()
    {
        return $this->hasMany(OrganizationEntity::class);
    }

    public function interpretes()
    {
        return $this->morphedByMany(Interprete::class, 'entity', 'organization_entities')
            ->withPivot('is_primary')->withTimestamps();
    }

    public function festivales()
    {
        return $this->morphedByMany(Festival::class, 'entity', 'organization_entities')
            ->withPivot('is_primary')->withTimestamps();
    }

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            if (empty($organization->slug)) {
                $organization->slug = Str::slug($organization->name);
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function socialAccounts()
    {
        return $this->morphMany(SocialAccount::class, 'owner');
    }
}
