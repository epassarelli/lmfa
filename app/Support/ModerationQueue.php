<?php

namespace App\Support;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\Event;
use App\Models\EventProgram;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\News;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;

/**
 * Definición única de "pendiente de revisión". La usan el Centro de Moderación (listados) y el
 * tablero del administrador (contadores), para que siempre cuenten lo mismo.
 */
class ModerationQueue
{
    public static function news(): Builder
    {
        return News::query()->where(fn ($q) => $q->where('editorial_status', 'draft')->orWhereNull('published_at'));
    }

    public static function events(): Builder
    {
        return Event::query()->where('editorial_status', 'draft');
    }

    public static function programs(): Builder
    {
        return EventProgram::query()->where('editorial_status', 'draft');
    }

    public static function venues(): Builder
    {
        return Venue::query()->where('status', 'pending');
    }

    public static function interpretes(): Builder
    {
        return Interprete::query()->where('estado', 0);
    }

    public static function albums(): Builder
    {
        return Album::query()->where('estado', 0);
    }

    public static function canciones(): Builder
    {
        return Cancion::query()->where('estado', 0);
    }

    public static function festivales(): Builder
    {
        return Festival::query()->where('status', 'draft');
    }

    /**
     * @return array{items: array<string, int>, total: int, oldest: ?int}
     */
    public static function counts(): array
    {
        $items = [
            'Noticias' => self::news()->count(),
            'Eventos' => self::events()->count(),
            'Jornadas' => self::programs()->count(),
            'Salas' => self::venues()->count(),
            'Artistas' => self::interpretes()->count(),
            'Discos' => self::albums()->count(),
            'Canciones' => self::canciones()->count(),
            'Festivales' => self::festivales()->count(),
        ];

        return ['items' => $items, 'total' => array_sum($items), 'oldest' => null];
    }
}
