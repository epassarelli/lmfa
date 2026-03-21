<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Perfiles de Imágenes
    |--------------------------------------------------------------------------
    |
    | Aquí se definen los diferentes perfiles de imágenes del sistema.
    | Cada perfil puede tener múltiples variantes con diferentes ratios y tamaños.
    |
    */

    'news_full' => [
        'variants' => [
            'card' => [
                'ratio' => [16, 9],
                'sizes' => [320, 480, 768],
            ],
            'detail' => [
                'ratio' => [16, 9],
                'sizes' => [768, 1200, 1600],
            ],
            'sidebar' => [
                'ratio' => [1, 1],
                'sizes' => [120, 240, 320],
            ],
        ],
    ],

    'artist' => [
        'variants' => [
            'main' => [
                'ratio' => [3, 4],
                'sizes' => [300, 450, 768],
            ],
        ],
    ],

    'hero' => [
        'variants' => [
            'main' => [
                'ratio' => [16, 9],
                'sizes' => [768, 1280, 1600, 1920],
            ],
        ],
    ],

    'album' => [
        'variants' => [
            'main' => [
                'ratio' => [1, 1],
                'sizes' => [300, 600, 800],
            ],
        ],
    ],

    'recipe' => [
        'variants' => [
            'main' => [
                'ratio' => [4, 3],
                'sizes' => [400, 800],
            ],
        ],
    ],

];
