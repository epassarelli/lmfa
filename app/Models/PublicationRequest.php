<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicationRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING    = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_FAILED     = 'failed';
    const STATUS_CANCELLED  = 'cancelled';

    protected $fillable = [
        'content_type',
        'content_id',
        'requested_by',
        'mode',
        'wants_portal_publish',
        'wants_portal_social',
        'wants_own_social',
        'scheduled_at',
        'reminder_policy',
        'status'
    ];

    protected $casts = [
        'wants_portal_publish' => 'boolean',
        'wants_portal_social' => 'boolean',
        'wants_own_social' => 'boolean',
        'scheduled_at' => 'datetime',
    ];

    public function content()
    {
        return $this->morphTo();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function targets()
    {
        return $this->hasMany(PublicationTarget::class);
    }
}
