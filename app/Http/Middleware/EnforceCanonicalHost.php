<?php

namespace App\Http\Middleware;

use App\Support\CanonicalUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        $target = CanonicalUrl::redirectTarget($request->fullUrl());

        if ($target !== $request->fullUrl()) {
            return redirect()->away($target, 301);
        }

        return $next($request);
    }

    protected function shouldBypass(Request $request): bool
    {
        $host = strtolower($request->getHost());

        if ($host === '') {
            return true;
        }

        if (in_array($host, config('seo.ignored_hosts', []), true)) {
            return true;
        }

        return str_ends_with($host, '.test') || str_ends_with($host, '.localhost');
    }
}
