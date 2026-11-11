<?php

return [
    'enabled' => env('SOCIAL_PUBLICATION_ENABLED', false),
    'destination' => 'facebook:page:'.env('SOCIAL_PUBLICATION_FACEBOOK_PAGE_ID', '145931542128207'),
    'instagram_enabled' => env('SOCIAL_PUBLICATION_INSTAGRAM_ENABLED', false),
    'instagram_destination' => 'instagram:account:'.env('SOCIAL_PUBLICATION_INSTAGRAM_ACCOUNT_ID', '17841400312117345'),
    'instagram_after_id' => 832,
    'operators' => array_filter(explode(',', env('SOCIAL_PUBLICATION_OPERATOR_IDS', ''))),
    'asset_hosts' => array_filter(explode(',', env('SOCIAL_PUBLICATION_ASSET_HOSTS', 'mifolkloreargentino.com'))),
    'lease_seconds' => 600,
    'news_automatic' => env('SOCIAL_PUBLICATION_NEWS_AUTOMATIC', true),
    'news_after_id' => (int) env('SOCIAL_PUBLICATION_NEWS_AFTER_ID', 833),
    'templates' => ['news' => 'mfa-noticias-v4', 'events' => 'mfa-agenda-v4'],
    'catalog_version' => '4.0.0',
    'font_regular' => env('SOCIAL_PUBLICATION_FONT_REGULAR', '/tmp/mfa-fonts/segoeui.ttf'),
    'font_bold' => env('SOCIAL_PUBLICATION_FONT_BOLD', '/tmp/mfa-fonts/segoeuib.ttf'),
];
