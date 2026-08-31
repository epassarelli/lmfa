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
            1 => 'img/logo-share.jpg',
            2 => 'img/logo-share.jpg',
            3 => 'img/logo-share.jpg',
            4 => 'img/logo-share.jpg',
            5 => 'img/logo-share.jpg',
            'default' => 'img/logo-share.jpg',
        ],
        'event' => 'img/logo-share.jpg',
        'festival' => 'img/logo-share.jpg',
        'artist' => 'img/logo-share.jpg',
        'knowledge' => 'img/logo-share.jpg',
        'default' => 'img/logo-share.jpg',
    ],
];
