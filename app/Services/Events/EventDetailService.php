<?php

namespace App\Services\Events;

use App\Models\Event;
use App\Models\Interprete;
use App\Models\Provincia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Datos de navegación y continuidad de la ficha pública de un evento.
 * Todas las consultas van acotadas con limit en SQL y con eager loading.
 */
final class EventDetailService
{
    public const PROVINCES_CACHE_KEY = 'shows:provincias';

    public const UPCOMING_PROVINCES_CACHE_KEY = 'shows:provinces-with-upcoming';

    private const CONTINUITY_LIMIT = 3;

    /**
     * Provincias ordenadas por nombre, cacheadas (catálogo estable de ~24 filas).
     */
    public function provincias(): Collection
    {
        return Cache::remember(self::PROVINCES_CACHE_KEY, now()->addDay(), fn () => Provincia::orderBy('nombre')->get());
    }

    /**
     * Intérpretes activos para el datalist del buscador de la cartelera, cacheados.
     */
    public function interpretes(): Collection
    {
        return Cache::remember('shows:index:interpretes', now()->addHour(), fn () => Interprete::active()->get());
    }

    public function findProvinciaBySlug(string $slug): ?Provincia
    {
        $normalized = \Illuminate\Support\Str::slug($slug);

        return $this->provincias()->first(fn (Provincia $provincia) => $provincia->slug === $normalized);
    }

    public function findProvinciaById(int|string|null $id): ?Provincia
    {
        return $id ? $this->provincias()->firstWhere('id', (int) $id) : null;
    }

    /**
     * Provincias con eventos públicos futuros y su cantidad, cacheadas.
     *
     * @return array<int, array{id:int, nombre:string, slug:string, url:string, count:int}>
     */
    public function provincesWithUpcomingEvents(): array
    {
        return Cache::remember(self::UPCOMING_PROVINCES_CACHE_KEY, now()->addHour(), function () {
            $counts = Event::publiclyVisible()
                ->where('start_at', '>=', now()->startOfDay())
                ->whereNotNull('province_id')
                ->groupBy('province_id')
                ->select('province_id', DB::raw('COUNT(*) as total'))
                ->pluck('total', 'province_id');

            return $this->provincias()
                ->filter(fn (Provincia $provincia) => $counts->has($provincia->id))
                ->map(fn (Provincia $provincia) => [
                    'id' => (int) $provincia->id,
                    'nombre' => $provincia->nombre,
                    'slug' => $provincia->slug,
                    'url' => url('/cartelera-de-eventos-folkloricos/'.$provincia->slug),
                    'count' => (int) $counts[$provincia->id],
                ])
                ->values()
                ->all();
        });
    }

    /**
     * pasado: la fecha de fin (o de inicio) es anterior a hoy.
     * en curso: empezó y su fecha de fin todavía no pasó.
     */
    public function status(Event $event): string
    {
        $start = $event->start_at;
        $end = $event->end_at && $start && $event->end_at->greaterThan($start) ? $event->end_at : $start;

        if (! $start) {
            return 'upcoming';
        }

        if ($end->copy()->endOfDay()->isPast()) {
            return 'past';
        }

        if ($start->isPast() && $event->end_at && $event->end_at->greaterThan($start) && ! $start->isToday()) {
            return 'ongoing';
        }

        return 'upcoming';
    }

    /**
     * Bloque prioritario "Más de {artista}": próximas fechas del artista principal;
     * si no tiene, sus presentaciones anteriores. Nunca incluye el evento actual.
     *
     * @return array{artist: ?Interprete, mode: ?string, events: Collection}
     */
    public function artistContinuity(Event $event): array
    {
        /** @var Interprete|null $artist */
        $artist = $event->interpretes->first();

        if (! $artist) {
            return ['artist' => null, 'mode' => null, 'events' => collect()];
        }

        $upcoming = $this->artistEvents($artist, $event)
            ->where('start_at', '>=', now()->startOfDay())
            ->orderBy('start_at')
            ->limit(self::CONTINUITY_LIMIT)
            ->get();

        if ($upcoming->isNotEmpty()) {
            return ['artist' => $artist, 'mode' => 'upcoming', 'events' => $upcoming];
        }

        $previous = $this->artistEvents($artist, $event)
            ->where('start_at', '<', now()->startOfDay())
            ->orderByDesc('start_at')
            ->limit(self::CONTINUITY_LIMIT)
            ->get();

        return ['artist' => $artist, 'mode' => $previous->isNotEmpty() ? 'previous' : null, 'events' => $previous];
    }

    /**
     * Próximos eventos públicos de la misma provincia, sin el actual ni los ya listados.
     */
    public function upcomingInProvince(Event $event, array $excludeIds = []): Collection
    {
        if (! $event->province_id) {
            return collect();
        }

        return Event::publiclyVisible()
            ->where('province_id', $event->province_id)
            ->where('start_at', '>=', now()->startOfDay())
            ->whereNotIn('events.id', array_merge([$event->id], $excludeIds))
            ->with($this->cardRelations())
            ->orderBy('start_at')
            ->limit(self::CONTINUITY_LIMIT)
            ->get();
    }

    private function artistEvents(Interprete $artist, Event $event)
    {
        return $artist->events()
            ->publiclyVisible()
            ->where('events.id', '<>', $event->id)
            ->with($this->cardRelations());
    }

    private function cardRelations(): array
    {
        return [
            'images',
            'provincia',
            'interpretes' => fn ($query) => $query->where('estado', 1)->with('images')->orderBy('event_interprete.sort_order'),
        ];
    }
}
