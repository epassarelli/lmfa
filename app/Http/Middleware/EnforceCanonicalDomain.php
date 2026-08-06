<?php

namespace App\Http\Middleware;

use App\Support\CanonicalUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceCanonicalDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        $target = CanonicalUrl::redirectTarget($request->fullUrl());

        if ($target !== $request->fullUrl()) {
            return redirect()->to($target, 301);
        }

        return $next($request);
    }

    protected function shouldBypass(Request $request): bool
    {
        $host = strtolower((string) $request->getHost());
        $ignoredHosts = array_map('strtolower', config('seo.ignored_hosts', []));

        return in_array($host, $ignoredHosts, true);
    }
}
