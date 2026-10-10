<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationState;
use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;

class EventProgram extends Model
{
    use HasMedia, HasPublicationState;

    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'venue_id', 'ticket_url', 'price_text', 'is_free', 'editorial_status', 'published_at', 'created_by'];

    protected $casts = ['is_free' => 'boolean', 'published_at' => 'datetime'];

    public function events()
    {
        return $this->hasMany(Event::class, 'program_id');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePubliclyVisible($query)
    {
        return $query->publishedVisible()->whereHas('events', fn ($q) => $q->publiclyVisible());
    }
}
