<?php

return [
    'enabled' => env('SOCIAL_PUBLICATION_ENABLED', false),
    'destination' => 'facebook:page:'.env('SOCIAL_PUBLICATION_FACEBOOK_PAGE_ID', '145931542128207'),
    'operators' => array_filter(explode(',', env('SOCIAL_PUBLICATION_OPERATOR_IDS', ''))),
    'asset_hosts' => array_filter(explode(',', env('SOCIAL_PUBLICATION_ASSET_HOSTS', 'mifolkloreargentino.com'))),
    'lease_seconds' => 600,
    'templates' => ['news' => 'mfa-noticias-v4', 'events' => 'mfa-agenda-v4'],
    'catalog_version' => '4.0.0',
    'font_regular' => env('SOCIAL_PUBLICATION_FONT_REGULAR', '/tmp/mfa-fonts/segoeui.ttf'),
    'font_bold' => env('SOCIAL_PUBLICATION_FONT_BOLD', '/tmp/mfa-fonts/segoeuib.ttf'),
];
