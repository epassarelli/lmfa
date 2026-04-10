<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\HasMedia;

class News extends Model
{
    use HasFactory, HasMedia;

    protected $table = 'news';

    protected $fillable = [
        'organization_id',
        'title',
        'slug',
        'subtitle',
        'excerpt',
        'body',
        'news_type',
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
        'interprete_id',
        'categoria_id',
        'visitas',
        'estado',
        // Compatibility
        'titulo',
        'noticia',
        'foto',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title . '-' . now()->timestamp);
            }
        });
    }

    // -------------------------------------------------------
    // Compatibility accessors (old noticias field names)
    // -------------------------------------------------------

    public function getTituloAttribute()
    {
        return $this->title;
    }

    public function setTituloAttribute($value)
    {
        $this->title = $value;
    }

    public function getNoticiaAttribute()
    {
        return $this->body;
    }

    public function setNoticiaAttribute($value)
    {
        $this->body = $value;
    }

    public function getFotoAttribute()
    {
        return $this->featured_image_path;
    }

    public function setFotoAttribute($value)
    {
        $this->featured_image_path = $value;
    }

    public function getFechaPublicacionAttribute()
    {
        return $this->published_at ?? $this->created_at;
    }

    // -------------------------------------------------------
    // Relations
    // -------------------------------------------------------

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación principal de categoría (nombre canónico en inglés).
     */
    public function category()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Alias de category() para compatibilidad con vistas que usan $noticia->categoria.
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Intérprete principal (campo directo interprete_id).
     */
    public function interprete()
    {
        return $this->belongsTo(Interprete::class);
    }

    /**
     * Intérpretes secundarios via tabla pivote interprete_noticia.
     */
    public function interpretes()
    {
        return $this->belongsToMany(Interprete::class, 'interprete_noticia', 'noticia_id', 'interprete_id');
    }
}
