<?php

namespace App\Services\Product;

use App\Models\Festival;
use App\Models\Event;
use App\Models\Interprete;
use Illuminate\Support\Collection;

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

    public function forEvent(Event $event): array
    {
        $festivals = $event->festivales()
            ->publishedVisible()
            ->whereIn('festivales.id', $this->allowlist())
            ->with(['provincia', 'mes', 'locality', 'images'])
            ->limit(2)
            ->get();

        if (! config('features.festival_journey', false) || $festivals->isEmpty()) {
            return ['enabled' => false, 'festivals' => collect(), 'artists' => collect()];
        }

        return [
            'enabled' => true,
            'festivals' => $festivals,
            'artists' => $event->interpretes()->where('estado', 1)->with('images')->orderBy('interprete')->limit(6)->get(),
        ];
    }

    public function forArtist(Interprete $artist): array
    {
        if (! config('features.festival_journey', false)) {
            return ['enabled' => false, 'events' => collect(), 'festivals' => collect()];
        }

        $allowlist = $this->allowlist();

        return [
            'enabled' => true,
            'events' => $artist->events()->publiclyVisible()->where('start_at', '>=', now()->startOfDay())
                ->whereHas('festivales', fn ($query) => $query->publishedVisible()->whereIn('festivales.id', $allowlist))
                ->with(['images', 'provincia', 'interpretes' => fn ($query) => $query->where('estado', 1)->with('images')])
                ->orderBy('start_at')->limit(3)->get(),
            'festivals' => $artist->festivales()->publishedVisible()->whereIn('festivales.id', $allowlist)
                ->with(['images', 'provincia', 'mes', 'locality'])->orderBy('title')->limit(3)->get(),
        ];
    }

    private function allowlist(): array
    {
        return config('features.festival_journey_allowlist', []);
    }
}
