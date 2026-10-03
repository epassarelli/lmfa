<?php

namespace App\Services\Events;

use App\Models\Event;
use App\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Entidades vinculadas a un evento para su ficha pública. Solo contenido público,
 * con límites en SQL. Las noticias se derivan del festival y de los artistas del
 * evento (no existe un pivot evento–noticia).
 */
final class EventRelatedContentService
{
    private const FESTIVALS_LIMIT = 2;

    private const ARTICLES_LIMIT = 4;

    private const PENIAS_LIMIT = 4;

    private const NEWS_LIMIT = 3;

    /**
     * Requiere los intérpretes activos del evento ya cargados.
     *
     * @return array{festivals: Collection, knowledgeArticles: Collection, penias: Collection, news: Collection}
     */
    public function forEvent(Event $event): array
    {
        $festivals = $event->festivales()
            ->publishedVisible()
            ->with(['provincia', 'mes', 'locality', 'images'])
            ->orderBy('title')
            ->limit(self::FESTIVALS_LIMIT)
            ->get();

        $knowledgeArticles = $event->knowledgeArticles()
            ->visible()
            ->with('category')
            ->latest('knowledge_articles.published_at')
            ->limit(self::ARTICLES_LIMIT)
            ->get();

        $penias = config('features.penia_directory', false)
            ? $event->peniaProfiles()->publiclyVisible()->orderBy('title')->limit(self::PENIAS_LIMIT)->get()
            : collect();

        return [
            'festivals' => $festivals,
            'knowledgeArticles' => $knowledgeArticles,
            'penias' => $penias,
            'news' => $this->derivedNews($festivals->modelKeys(), $event->interpretes->modelKeys()),
        ];
    }

    private function derivedNews(array $festivalIds, array $artistIds): Collection
    {
        if ($festivalIds === [] && $artistIds === []) {
            return collect();
        }

        return News::query()
            ->publishedVisible()
            ->where(function (Builder $query) use ($festivalIds, $artistIds) {
                if ($artistIds !== []) {
                    $query->whereIn('news.interprete_id', $artistIds)
                        ->orWhereHas('interpretes', fn (Builder $relation) => $relation->whereIn('interpretes.id', $artistIds));
                }

                if ($festivalIds !== []) {
                    $query->orWhereHas('festivales', fn (Builder $relation) => $relation->whereIn('festivales.id', $festivalIds));
                }
            })
            ->with('interprete:id,slug,interprete')
            ->latest('news.published_at')
            ->limit(self::NEWS_LIMIT)
            ->get();
    }
}
