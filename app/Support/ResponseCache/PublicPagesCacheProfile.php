<?php

namespace App\Support\ResponseCache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

/**
 * Perfil de cacheo para paginas publicas.
 *
 * Hereda de CacheAllSuccessfulGetRequests (el perfil por defecto del
 * paquete) y le suma dos reglas criticas que el default NO trae:
 *
 * 1. Nunca cachear si hay un usuario logueado -- evita servirle a un
 *    visitante anonimo una respuesta pensada para otra sesion.
 * 2. Nunca cachear rutas con formularios que todavia dependen de un
 *    @csrf renderizado en el servidor (contacto, login/registro) o
 *    rutas que no aportan valor cacheadas (buscador, csrf-refresh).
 *    El newsletter NO esta en esta lista porque su formulario ya se
 *    resolvio de forma "cache-safe" (ver newsletter-form.blade.php).
 */
class PublicPagesCacheProfile extends CacheAllSuccessfulGetRequests
{
    protected array $excludedPatterns = [
        'contacto*',
        'login',
        'register',
        'password/*',
        'email/*',
        'two-factor-challenge',
        'csrf-refresh',
        'buscar*',
        'newsletter/unsubscribe/*',
    ];

    public function shouldCacheRequest(Request $request): bool
    {
        if (! parent::shouldCacheRequest($request)) {
            return false;
        }

        if ($request->user()) {
            return false;
        }

        foreach ($this->excludedPatterns as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }
}
