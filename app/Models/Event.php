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
        // Compatibility (nombres legacy del esquema shows)
        'show',
        'detalles',
        'fecha',
        'lugar',
        'direccion',
        'user_id',
        'publicar',
        'estado',
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

    public function getEstadoAttribute($value): int
    {
        if ($value !== null) return (int) $value;
        return $this->editorial_status === 'published' ? 1 : 0;
    }

    public function setEstadoAttribute($value): void
    {
        $this->attributes['estado'] = $value;
        $this->attributes['editorial_status'] = $value ? 'published' : 'draft';
    }

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

    public function setFechaAttribute($value)
    {
        $this->attributes['start_at'] = $value;
    }

    public function getLugarAttribute()
    {
        return $this->city;
    }

    public function setLugarAttribute($value)
    {
        $this->attributes['city'] = $value;
    }

    public function getDireccionAttribute()
    {
        return $this->address;
    }

    public function setDireccionAttribute($value)
    {
        $this->attributes['address'] = $value;
    }

    public function getUserIdAttribute()
    {
        return $this->created_by;
    }

    public function setUserIdAttribute($value)
    {
        $this->attributes['created_by'] = $value;
    }

    public function getPublicarAttribute()
    {
        return $this->published_at;
    }

    public function setPublicarAttribute($value)
    {
        $this->attributes['published_at'] = $value;
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

    /**
     * Alias de creator() para compatibilidad con eager-loads legacy.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function interpretes()
    {
        // event_interprete NO tiene columnas de timestamps — no usar withTimestamps()
        return $this->belongsToMany(Interprete::class, 'event_interprete', 'event_id', 'interprete_id')
                    ->withPivot('sort_order');
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'province_id');
    }
}
