<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicationAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_target_id',
        'attempt_number',
        'started_at',
        'finished_at',
        'request_payload_json',
        'response_payload_json',
        'external_post_id',
        'external_url',
        'status',
        'error_code',
        'error_message',
        'is_retryable'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'request_payload_json' => 'array',
        'response_payload_json' => 'array',
        'is_retryable' => 'boolean',
    ];

    public function target()
    {
        return $this->belongsTo(PublicationTarget::class, 'publication_target_id');
    }
}
