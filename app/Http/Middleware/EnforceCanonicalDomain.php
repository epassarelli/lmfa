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
        $current = $this->currentPublicUrl($request);

        if ($target !== $current) {
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

    protected function currentPublicUrl(Request $request): string
    {
        $scheme = $request->getScheme();
        $host = $request->getHost();
        $port = $request->getPort();
        $requestUri = $request->getRequestUri() ?: '/';

        $authority = $scheme.'://'.$host;

        if (! $this->isDefaultPort($scheme, $port)) {
            $authority .= ':'.$port;
        }

        if ($requestUri[0] !== '/') {
            $requestUri = '/'.$requestUri;
        }

        return $authority.$requestUri;
    }

    protected function isDefaultPort(string $scheme, int $port): bool
    {
        return ($scheme === 'https' && $port === 443)
            || ($scheme === 'http' && $port === 80);
    }
}
