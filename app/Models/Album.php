<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Interprete;
use App\Models\Cancion;
use App\Models\User;
use App\Traits\CommonMethodsTrait;

class Album extends Model
{
    use CommonMethodsTrait;
    use \App\Traits\ScopesToManagedInterprete;
    use HasFactory;
    protected $table = 'albunes';
    protected $casts = [
        'estado' => 'boolean',
        'visitas' => 'integer',
        'release_date' => 'date',
    ];

    protected $fillable = [
        'album',
        'album_type',
        'slug',
        'anio',
        'excerpt',
        'label',
        'release_date',
        'seo_title',
        'meta_description',
        'foto',
        'image_alt',
        'spotify',
        'visitas',
        'estado',
        'user_id',
        'interprete_id'
    ];

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function interprete()
    {
        return $this->belongsTo(Interprete::class, 'interprete_id');
    }

    public function canciones()
    {
        return $this->belongsToMany(Cancion::class, 'albunes_canciones')
            ->withPivot('orden')
            ->orderBy('pivot_orden');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias de lectura `title` → `album` para el código genérico de publicaciones en redes (log, cola, ejecutor).
     * No hay columna `title`; si una consulta trae el alias explícito, se respeta.
     */
    public function getTitleAttribute(): ?string
    {
        return $this->attributes['title'] ?? $this->attributes['album'] ?? null;
    }

    public function getUrl(): string
    {
        if ($this->interprete) {
            return route('artista.disco', [
                'interprete' => $this->interprete->slug,
                'slug' => $this->slug
            ]);
        }

        return route('discografias.index'); // fallback o error si no hay interprete
    }
}
