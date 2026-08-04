<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KnowledgeArticle extends Model
{
    use HasFactory, HasMedia, SoftDeletes;

    protected $fillable = [
        'knowledge_category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image_id',
        'featured_image_path',
        'image_alt',
        'seo_title',
        'meta_description',
        'primary_keyword',
        'secondary_keywords',
        'editorial_status',
        'published_at',
        'last_verified_at',
        'author_id',
        'reviewed_by',
        'visits',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'visits' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $article) {
            if (blank($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'knowledge_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function interpretes(): BelongsToMany
    {
        return $this->belongsToMany(Interprete::class, 'knowledge_article_interprete')->withTimestamps();
    }

    public function canciones(): BelongsToMany
    {
        return $this->belongsToMany(Cancion::class, 'knowledge_article_cancion')->withTimestamps();
    }

    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class, 'knowledge_article_album')->withTimestamps();
    }

    public function festivales(): BelongsToMany
    {
        return $this->belongsToMany(Festival::class, 'knowledge_article_festival')->withTimestamps();
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_knowledge_article')->withTimestamps();
    }

    public function provincias(): BelongsToMany
    {
        return $this->belongsToMany(Provincia::class, 'knowledge_article_provincia')->withTimestamps();
    }

    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'knowledge_article_related',
            'knowledge_article_id',
            'related_knowledge_article_id'
        )->withTimestamps();
    }

    public function scopePublished($query)
    {
        return $query
            ->where('editorial_status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeVisible($query)
    {
        return $query->published()->whereNull('deleted_at');
    }

    public function getUrl(): string
    {
        return route('enciclopedia.show', [
            'categorySlug' => $this->category?->slug,
            'articleSlug' => $this->slug,
        ]);
    }

    public function publish(?User $reviewer = null): void
    {
        $this->forceFill([
            'editorial_status' => 'published',
            'published_at' => $this->published_at ?? now(),
            'reviewed_by' => $reviewer?->id ?? $this->reviewed_by,
        ])->save();
    }

    public function unpublish(?User $reviewer = null): void
    {
        $this->forceFill([
            'editorial_status' => 'draft',
            'reviewed_by' => $reviewer?->id ?? $this->reviewed_by,
        ])->save();
    }
}
