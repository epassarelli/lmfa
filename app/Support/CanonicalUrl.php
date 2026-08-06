<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CanonicalUrl
{
    public static function current(): string
    {
        return static::normalize(request()?->fullUrl());
    }

    public static function redirectTarget(?string $url = null): string
    {
        return static::normalize($url, false);
    }

    public static function normalize(?string $url = null, bool $stripTracking = true): string
    {
        $baseUrl = rtrim((string) config('seo.base_url', 'https://mifolkloreargentino.com'), '/');
        $target = $url ?: $baseUrl.'/';

        if (Str::startsWith($target, '/')) {
            $target = $baseUrl.$target;
        }

        $parts = parse_url($target) ?: [];
        $baseParts = parse_url($baseUrl) ?: [];

        $path = $parts['path'] ?? '/';
        $query = $stripTracking
            ? static::filteredQuery($parts['query'] ?? '')
            : ($parts['query'] ?? '');

        $normalized = ($baseParts['scheme'] ?? 'https').'://'.($baseParts['host'] ?? 'mifolkloreargentino.com');
        $normalized .= $path !== '' ? $path : '/';

        if ($query !== '') {
            $normalized .= '?'.$query;
        }

        return $normalized;
    }

    public static function asset(?string $path = null): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (! Str::startsWith($path, ['http://', 'https://', '/'])) {
            $path = '/'.$path;
        }

        return static::normalize($path, false);
    }

    protected static function filteredQuery(string $query): string
    {
        if ($query === '') {
            return '';
        }

        parse_str($query, $parameters);

        $filtered = Arr::where($parameters, function ($value, $key) {
            return ! static::isTrackingParameter((string) $key);
        });

        return http_build_query($filtered, '', '&', PHP_QUERY_RFC3986);
    }

    protected static function isTrackingParameter(string $key): bool
    {
        $normalized = Str::lower($key);
        $tracked = array_map('strtolower', config('seo.tracking_parameters', []));

        if (in_array($normalized, $tracked, true)) {
            return true;
        }

        return Str::startsWith($normalized, 'utm_');
    }
}
