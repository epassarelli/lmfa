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
            1 => 'img/fallbacks/news-actualidad-v2.webp',
            2 => 'img/fallbacks/news-festivales-v2.webp',
            3 => 'img/fallbacks/news-lanzamientos-v2.webp',
            4 => 'img/fallbacks/artist-default-v2.webp',
            5 => 'img/fallbacks/news-cartelera-v2.webp',
            'default' => 'img/fallbacks/news-actualidad-v2.webp',
        ],
        'event' => 'img/fallbacks/event-default-v2.webp',
        'festival' => 'img/fallbacks/festival-default-v2.webp',
        'artist' => 'img/fallbacks/artist-default-v2.webp',
        'knowledge' => 'img/fallbacks/knowledge-default-v2.webp',
        'album' => 'img/fallbacks/album-default-v2.webp',
        'recipe' => 'img/fallbacks/recipe-default-v2.webp',
        'myth' => 'img/fallbacks/myth-default-v2.webp',
        'default' => 'img/fallbacks/news-actualidad-v2.webp',
    ],
];
