<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\News;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\User;

use App\Traits\CommonMethodsTrait;

class Interprete extends Model
{
    use CommonMethodsTrait;

    use HasFactory;
    // protected $fillable = ['interprete', 'slug', 'biografia', 'foto', 'visitas', 'publicar', 'user_id', 'estado'];
    protected $fillable = [
        'interprete',
        'artist_type',
        'slug',
        'biografia',
        'excerpt',
        'seo_title',
        'meta_description',
        'foto',
        'image_alt',
        'telefono',
        'correo',
        'facebook',
        'instagram',
        'twitter',
        'youtube',
        'web',
        'visitas',
        'estado',
        'user_id'
    ];

    // Retorna una coleccion con interpretes menos el actual
    public static function getInterpretesExcluding($currentInterpreteId)
    {
        return Cache::remember('interpretes:active:list', 3600, function () {
            return self::query()
                ->where('estado', 1)
                ->orderBy('interprete', 'asc')
                ->get(['id', 'interprete', 'slug']);
        })->reject(fn ($interprete) => (int) $interprete->id === (int) $currentInterpreteId)->values();
    }

    // Definir un scope para filtrar intérpretes activos y ordenarlos por el campo 'interprete'
    public function scopeActive($query)
    {
        return $query->where('estado', 1)
            ->orderBy('interprete', 'asc')
            ->select('id', 'interprete', 'slug');
    }

    public function noticias()
    {
        return $this->belongsToMany(News::class, 'interprete_noticia', 'interprete_id', 'noticia_id');
    }

    public function noticiasRelacionadas(): Builder
    {
        return News::query()
            ->forInterprete($this)
            ->publishedVisible()
            ->with(['categoria', 'interprete', 'interprete.images', 'interpretes', 'images']);
    }

    /**
     * @deprecated Usar events() — la tabla shows ya no se usa como fuente de datos.
     */
    public function shows()
    {
        return $this->events();
    }

    public function events()
    {
        // event_interprete NO tiene columnas de timestamps — no usar withTimestamps()
        return $this->belongsToMany(Event::class, 'event_interprete', 'interprete_id', 'event_id')
                    ->withPivot('sort_order');
    }

    public function discos()
    {
        return $this->hasMany(Album::class);
    }

    public function canciones()
    {
        return $this->hasMany(Cancion::class);
    }

    public function festivales()
    {
        return $this->belongsToMany(Festival::class, 'festival_interprete');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
