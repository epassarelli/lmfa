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
 * 3. Nunca cachear rutas protegidas por 'auth' (admin y las de usuario
 *    logueado en avisos clasificados). La regla del punto 1 no alcanza
 *    para cubrirlas: CacheResponse corre en el stack global, ANTES de
 *    StartSession (ver App\Http\Kernel), asi que request()->user() es
 *    siempre null en el momento en que se decide cachear -- la regla
 *    "nunca cachear si hay usuario logueado" nunca se dispara. Sin esta
 *    exclusion explicita, un solo GET sin sesion a /admin (bot, o el
 *    primer visitante del dia) cachea el 302 a /login -- el paquete
 *    trata los redirects como cacheables (ver
 *    CacheAllSuccessfulGetRequests::hasCacheableResponseCode) -- y ese
 *    302 viejo se le sirve despues a cualquiera, logueado o no, sin que
 *    la sesion real se llegue a evaluar.
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
        'penias*',
        'radios-de-folklore-argentino*',
        'sitemap-penias.xml',
        'sitemap-radios.xml',
        'admin*',
        'avisos-clasificados/publicar',
        'avisos-clasificados/mis-avisos',
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
