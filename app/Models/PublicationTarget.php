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
        'priority'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

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
