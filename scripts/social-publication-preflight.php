<?php

// CLI only, read-only. Does not emit credentials, migrate, create users or enable content.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$schema = Illuminate\Support\Facades\Schema::class;
$checks = [
    'php' => PHP_VERSION,
    'gd' => extension_loaded('gd'),
    'font_regular_readable' => is_readable(config('social_publication.font_regular') ?? ''),
    'font_bold_readable' => is_readable(config('social_publication.font_bold') ?? ''),
    'catalog_readable' => is_readable(resource_path('social-templates/catalog.v4.json')),
    'eligibilities_table' => $schema::hasTable('social_publication_eligibilities'),
    'targets_extended' => $schema::hasColumn('publication_targets', 'destination_key'),
    'attempts_extended' => $schema::hasColumn('publication_attempts', 'request_key'),
    'enabled' => (bool) config('social_publication.enabled'),
    'instagram_enabled' => (bool) config('social_publication.instagram_enabled'),
    'instagram_route_registered' => Illuminate\Support\Facades\Route::has('social-publications.asset'),
    'operator_count' => count(config('social_publication.operators', [])),
    'public_app_url_is_https' => str_starts_with(config('app.url'), 'https://'),
];
echo json_encode($checks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
