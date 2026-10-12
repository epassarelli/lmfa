<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Concerns\HasPublicationState;
use App\Traits\HasMedia;

class Event extends Model
{
    use HasFactory, HasMedia, HasPublicationState;

    protected $fillable = [
        'organization_id',
        'venue_id',
        'program_id',
        'program_overrides',
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
        'program_overrides' => 'array',
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

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->publishedVisible()
            ->where('status', 'active');
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

    public function program()
    {
        return $this->belongsTo(EventProgram::class);
    }

    public function scopeUpcomingOrOngoing(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->where('start_at', '>=', now()->startOfDay())
            ->orWhere('end_at', '>=', now()->startOfDay()));
    }

    public function scopeOverlapping(Builder $query, $start, $end): Builder
    {
        return $query->where('start_at', '<=', $end)->where(fn ($q) => $q
            ->where('start_at', '>=', $start)->orWhere('end_at', '>=', $start));
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

    /**
     * Ids de los intérpretes vinculados (pivote event_interprete).
     *
     * @return array<int, int>
     */
    public function linkedInterpreteIds(): array
    {
        return $this->interpretes()->pluck('interpretes.id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Eventos donde el usuario administra al menos un artista vinculado y ninguno ajeno.
     */
    public function scopeEditableByMember(\Illuminate\Database\Eloquent\Builder $query, User $user): \Illuminate\Database\Eloquent\Builder
    {
        $managed = $user->managedInterpreteIdsSubquery();

        return $query
            ->whereHas('interpretes', fn ($q) => $q->whereIn('interpretes.id', $managed))
            ->whereDoesntHave('interpretes', fn ($q) => $q->whereNotIn('interpretes.id', $managed));
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

    public function festivales()
    {
        return $this->belongsToMany(Festival::class, 'event_festival');
    }

    public function knowledgeArticles()
    {
        return $this->belongsToMany(KnowledgeArticle::class, 'event_knowledge_article')->withTimestamps();
    }

    public function peniaProfiles()
    {
        return $this->belongsToMany(PeniaProfile::class, 'penia_profile_event')->withTimestamps();
    }
}
