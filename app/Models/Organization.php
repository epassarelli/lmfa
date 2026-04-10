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
        'status',
        'created_by',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

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
