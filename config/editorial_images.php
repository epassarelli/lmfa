<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Editorial image fallbacks
    |--------------------------------------------------------------------------
    |
    | These assets are used only after own and related entity images fail.
    | Keep them graphical/generic: they must not imply that they document a
    | real event, artist or publication.
    |
    */
    'fallbacks' => [
        'news' => [
            1 => 'img/fallbacks/news-actualidad.webp',
            2 => 'img/fallbacks/news-festivales.webp',
            3 => 'img/fallbacks/news-lanzamientos.webp',
            4 => 'img/fallbacks/news-entrevistas.webp',
            5 => 'img/fallbacks/news-cartelera.webp',
            'default' => 'img/fallbacks/news-default.webp',
        ],
        'event' => 'img/fallbacks/event-default.webp',
        'festival' => 'img/fallbacks/festival-default.webp',
        'artist' => 'img/fallbacks/artist-default.webp',
        'knowledge' => 'img/fallbacks/knowledge-default.webp',
        'default' => 'img/fallbacks/editorial-default.webp',
    ],
];
