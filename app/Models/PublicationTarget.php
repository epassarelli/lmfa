<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicationTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_request_id',
        'provider',
        'social_account_id',
        'destination_type',
        'template_variant',
        'scheduled_at',
        'status',
        'priority', 'ledger_content_kind', 'ledger_content_id', 'destination_key', 'executor',
        'published_at', 'external_post_id', 'external_url', 'lease_token_hash', 'lease_expires_at',
        'retry_after', 'attempts_count', 'last_error_code', 'last_error_message', 'content_hash',
        'payload_snapshot_json', 'template_id', 'template_catalog_version', 'asset_sha256', 'asset_reference'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime', 'lease_expires_at' => 'datetime', 'retry_after' => 'datetime',
        'payload_snapshot_json' => 'array',
    ];

    protected $hidden = ['lease_token_hash'];

    public function request()
    {
        return $this->belongsTo(PublicationRequest::class, 'publication_request_id');
    }

    public function socialAccount()
    {
        return $this->belongsTo(SocialAccount::class, 'social_account_id');
    }

    public function attempts()
    {
        return $this->hasMany(PublicationAttempt::class);
    }
}
