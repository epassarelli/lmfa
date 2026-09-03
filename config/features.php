<?php

return [
    // The journey remains dark until the editorial pilot is explicitly enabled.
    'festival_journey' => env('FEATURE_FESTIVAL_JOURNEY', false),
    'festival_journey_allowlist' => array_filter(array_map(
        'intval',
        explode(',', (string) env('FEATURE_FESTIVAL_JOURNEY_ALLOWLIST', ''))
    )),
    // Directorios en preparación: backoffice disponible, superficie pública oscura.
    'penia_directory' => env('FEATURE_PENIA_DIRECTORY', false),
    'radio_directory' => env('FEATURE_RADIO_DIRECTORY', false),
];
