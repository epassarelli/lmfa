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
        'visitas',
        'categoria_id',
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

    // Compatibility accessors
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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function interprete()
    {
        return $this->belongsTo(Interprete::class);
    }
}
