<?php

namespace App\Services\Product;

use Illuminate\Support\Collection;

final class FestivalJourney
{
    public function __construct(
        public readonly bool $enabled,
        public readonly Collection $upcomingEvents,
        public readonly Collection $artists,
        public readonly Collection $knowledgeArticles,
        public readonly Collection $news,
    ) {
    }

    public static function disabled(): self
    {
        return new self(false, collect(), collect(), collect(), collect());
    }
}
