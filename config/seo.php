<?php

return [
    'base_url' => env('SEO_BASE_URL', 'https://mifolkloreargentino.com'),

    'ignored_hosts' => [
        'localhost',
        'mfa.localhost',
        '127.0.0.1',
        '::1',
    ],

    'tracking_parameters' => [
        'fbclid',
        'gclid',
        'msclkid',
        'dclid',
        '_ga',
        '_gl',
        'mc_cid',
        'mc_eid',
    ],
];
