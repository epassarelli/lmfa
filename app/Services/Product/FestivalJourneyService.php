<?php

namespace App\Services\Product;

use App\Models\Festival;

final class FestivalJourneyService
{
    public function forFestival(Festival $festival): FestivalJourney
    {
        if (! $this->isEnabledFor($festival)) {
            return FestivalJourney::disabled();
        }

        return new FestivalJourney(
            true,
            $festival->events()
                ->publiclyVisible()
                ->where('start_at', '>=', now()->startOfDay())
                ->with(['images', 'provincia', 'interpretes' => fn ($query) => $query->where('estado', 1)->with('images')->orderBy('interprete')])
                ->orderBy('start_at')
                ->limit(3)
                ->get(),
            $festival->interpretes()
                ->where('estado', 1)
                ->with('images')
                ->orderBy('interprete')
                ->limit(6)
                ->get(),
            $festival->knowledgeArticles()
                ->visible()
                ->with(['category', 'images'])
                ->latest('published_at')
                ->limit(4)
                ->get(),
            $festival->noticias()
                ->publishedVisible()
                ->with(['categoria', 'images', 'interprete.images'])
                ->latest('published_at')
                ->limit(3)
                ->get(),
        );
    }

    private function isEnabledFor(Festival $festival): bool
    {
        return config('features.festival_journey', false)
            && in_array((int) $festival->getKey(), config('features.festival_journey_allowlist', []), true);
    }
}
