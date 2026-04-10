<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\HasMedia;

class Event extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = [
        'organization_id',
        'venue_id',
        'title',
        'subtitle',
        'excerpt',
        'body',
        'event_type',
        'modality',
        'slug',
        'start_at',
        'end_at',
        'timezone',
        'province_id',
        'city',
        'address',
        'latitude',
        'longitude',
        'ticket_url',
        'price_text',
        'is_free',
        'capacity',
        'status',
        'editorial_status',
        'publication_mode',
        'featured_image_id',
        'featured_image_path',
        'seo_title',
        'meta_description',
        'approved_by',
        'approved_at',
        'published_at',
        'created_by',
        'show', // Compatibility
        'detalles', // Compatibility
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'is_free' => 'boolean',
        'capacity' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title . '-' . now()->timestamp);
            }
        });
    }

    // -------------------------------------------------------
    // Compatibility accessors (old shows field names)
    // -------------------------------------------------------

    public function getTituloAttribute()
    {
        return $this->title;
    }

    public function getShowAttribute()
    {
        return $this->title;
    }

    public function setShowAttribute($value)
    {
        $this->title = $value;
    }

    public function getDetallesAttribute()
    {
        return $this->body;
    }

    public function getDetalleAttribute()
    {
        return $this->body;
    }

    public function setDetallesAttribute($value)
    {
        $this->body = $value;
    }

    public function getFechaAttribute()
    {
        return $this->start_at;
    }

    public function getLugarAttribute()
    {
        return $this->city;
    }

    public function getDireccionAttribute()
    {
        return $this->address;
    }

    /**
     * Primer intérprete asociado (para compatibilidad con show-card).
     * Requiere que la relación interpretes esté eager-loaded.
     */
    public function getInterpreteAttribute()
    {
        return $this->relationLoaded('interpretes') ? $this->interpretes->first() : null;
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function interpretes()
    {
        return $this->belongsToMany(Interprete::class, 'event_interprete', 'event_id', 'interprete_id')
                    ->withPivot('sort_order')
                    ->withTimestamps();
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'province_id');
    }
}
