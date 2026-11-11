<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class SocialPublicationOperator
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        $ability = $request->isMethod('GET') ? 'social-publications:read' : 'social-publications:execute';
        abort_unless($token instanceof PersonalAccessToken && !in_array('*', $token->abilities, true)
            && !$user->hasRole('administrador')
            && in_array($ability, $token->abilities, true)
            && in_array((string) $user->id, array_map('strval', config('social_publication.operators')), true)
            && $user->checkPermissionTo($ability), 403, 'Operador o permiso no autorizado.');
        if ($request->filled('destination_key')) {
            $instagram = $request->is('api/v1/social-publications/instagram/*');
            abort_unless($request->input('destination_key') === config($instagram ? 'social_publication.instagram_destination' : 'social_publication.destination'), 403);
        }
        if ($request->is('api/v1/social-publications/instagram/*')) {
            // Callbacks/status remain available while paused to settle an in-flight result.
            if (!$request->routeIs('social-publications.instagram.result', 'social-publications.instagram.show')) {
                abort_unless(config('social_publication.enabled') && config('social_publication.instagram_enabled'), 503, 'Instagram pausado.');
            }
        }
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');
        return $response;
    }
}
