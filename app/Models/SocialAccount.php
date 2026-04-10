<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_type',
        'owner_id',
        'provider',
        'account_name',
        'account_external_id',
        'page_or_profile_name',
        'token_encrypted',
        'access_token',
        'refresh_token_encrypted',
        'token_expires_at',
        'scopes_json',
        'status',
        'last_checked_at'
    ];

    protected $casts = [
        'token_encrypted' => 'encrypted',
        'refresh_token_encrypted' => 'encrypted',
        'token_expires_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'scopes_json' => 'array',
    ];

    /**
     * Get the owning model (User or Organization).
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * Helper to get the decrypted access token easily.
     */
    public function getAccessTokenAttribute()
    {
        return $this->token_encrypted; // Laravel cast encrypts/decrypts automatically
    }

    /**
     * Helper to set the decrypted access token easily.
     */
    public function setAccessTokenAttribute($value)
    {
        $this->token_encrypted = $value; // Trigger the encrypted cast
    }
}
