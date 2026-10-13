<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un envío de una edición del newsletter a un suscriptor (único por suscriptor y edición). */
class NewsletterDelivery extends Model
{
    protected $fillable = ['subscriber_id', 'edition_key', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscriber::class, 'subscriber_id');
    }
}
