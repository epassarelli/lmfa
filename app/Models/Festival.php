<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Festival extends Model
{
    use HasFactory, HasMedia;

    protected $table = 'festivales';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image_id',
        'featured_image_path',
        'seo_title',
        'meta_description',
        'status',
        'province_id',
        'locality_id',
        'mes_id',
        'user_id',
        'visitas',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $festival) {
            if (blank($festival->slug) && filled($festival->title)) {
                $festival->slug = Str::slug($festival->title);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'province_id');
    }

    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }

    public function mes()
    {
        return $this->belongsTo(Mes::class);
    }

    public function noticias()
    {
        return $this->belongsToMany(News::class, 'festival_news');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_festival');
    }

    public function interpretes()
    {
        return $this->belongsToMany(Interprete::class, 'festival_interprete');
    }

    public function knowledgeArticles()
    {
        return $this->belongsToMany(KnowledgeArticle::class, 'knowledge_article_festival', 'festival_id', 'knowledge_article_id');
    }

    public function scopePublishedVisible(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $builder) {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getUrl(): string
    {
        return route('festivales.show', $this->slug);
    }
}
