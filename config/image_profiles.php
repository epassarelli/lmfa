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
                'sizes' => [320, 480, 640, 768],
            ],
            'detail' => [
                'ratio' => [16, 9],
                'sizes' => [768, 1024, 1280, 1600],
            ],
            'sidebar' => [
                'ratio' => [1, 1],
                'sizes' => [120, 240],
            ],
        ],
    ],

    'artist' => [
        'variants' => [
            // card: usado en grillas h-96 (384px) y sidebar portrait 400px
            'card' => [
                'ratio' => [1, 1],
                'sizes' => [300, 400, 600, 800],
            ],
            // main: sidebar de página de detalle
            'main' => [
                'ratio' => [3, 4],
                'sizes' => [300, 400, 600, 768],
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
            // card: usado en grillas h-96 (384px) y sidebar 64px
            'card' => [
                'ratio' => [1, 1],
                'sizes' => [200, 300, 400, 600],
            ],
            // main: cover en página de detalle del disco
            'main' => [
                'ratio' => [1, 1],
                'sizes' => [400, 600, 800],
            ],
        ],
    ],

    'recipe' => [
        'variants' => [
            'card' => [
                'ratio' => [4, 3],
                'sizes' => [80, 160],
            ],
            'main' => [
                'ratio' => [4, 3],
                'sizes' => [400, 800],
            ],
        ],
    ],

    'festival' => [
        'variants' => [
            // card: grilla de festivales h-48 (~341px ancho mínimo)
            'card' => [
                'ratio' => [16, 9],
                'sizes' => [320, 480, 640, 768],
            ],
            // main: imagen grande en página de detalle
            'main' => [
                'ratio' => [16, 9],
                'sizes' => [768, 1024, 1200],
            ],
            // hero: hero full-width max-h-[500px]
            'hero' => [
                'ratio' => [16, 9],
                'sizes' => [768, 1024, 1280, 1600],
            ],
        ],
    ],

    'mito' => [
        'variants' => [
            'card' => [
                'ratio' => [4, 3],
                'sizes' => [160, 320],
            ],
            'main' => [
                'ratio' => [4, 3],
                'sizes' => [600, 900],
            ],
        ],
    ],

    'event' => [
        'variants' => [
            // card: grilla de shows h-96 (~682px ancho en 16:9)
            'card' => [
                'ratio' => [16, 9],
                'sizes' => [480, 640, 768, 1024],
            ],
            // main: imagen en página de detalle
            'main' => [
                'ratio' => [16, 9],
                'sizes' => [768, 1024, 1280],
            ],
            // hero: hero full-width max-h-[500px]
            'hero' => [
                'ratio' => [16, 9],
                'sizes' => [768, 1024, 1280, 1600],
            ],
            'sidebar' => [
                'ratio' => [1, 1],
                'sizes' => [120, 240],
            ],
        ],
    ],

];
