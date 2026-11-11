<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Un mail de reactivación a un usuario dentro de una tanda (lote). */
class ReengagementMailSend extends Model
{
    public const QUEUED = 'queued';
    public const SENT = 'sent';
    public const FAILED = 'failed';
    public const SKIPPED = 'skipped';

    public const VARIANT_LAPSED = 'lapsed';
    public const VARIANT_NEVER = 'never';

    protected $fillable = [
        'batch_uuid', 'user_id', 'email', 'variant', 'status', 'sent_at', 'error', 'triggered_by', 'created_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];
}
