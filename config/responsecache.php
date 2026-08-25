<?php

/*
 * IMPORTANTE: este archivo lo escribi sin poder correr
 * `composer require` ni `vendor:publish` en este entorno (sin acceso a
 * internet desde aca). Los nombres de clave de abajo son los que el
 * paquete spatie/laravel-responsecache viene usando de forma estable,
 * pero antes del primer deploy conviene diffearlo una vez contra
 * vendor/spatie/laravel-responsecache/config/responsecache.php (que se
 * genera solo al instalar el paquete) por si alguna clave cambio de
 * nombre en la version que quede instalada. El paquete hace
 * mergeConfigFrom(), asi que si falta alguna clave acá, cae al default
 * del paquete -- no rompe nada, en el peor caso una clave queda sin
 * personalizar.
 */

return [

    // Interruptor general. RESPONSE_CACHE_ENABLED=false en .env lo apaga
    // sin tocar codigo (util para debug en produccion).
    'enabled' => env('RESPONSE_CACHE_ENABLED', true),

    // Decide que requests se cachean y cuales no. Ver
    // App\Support\ResponseCache\PublicPagesCacheProfile: solo GET publicos,
    // nunca para usuarios logueados, y excluye contacto/login/buscador/etc.
    'cache_profile' => \App\Support\ResponseCache\PublicPagesCacheProfile::class,

    // TTL del cache de pagina. Se invalida antes igual apenas se publica o
    // edita contenido (ver AppServiceProvider::invalidateResponseCacheOnContentChanges),
    // asi que este numero es solo el techo maximo por las dudas.
    'cache_lifetime_in_seconds' => (int) env('RESPONSE_CACHE_LIFETIME', 60 * 60 * 6),

    // Agrega un header con la fecha de cacheo -- util para confirmar en
    // producción, desde curl/DevTools, si una respuesta vino del cache.
    'add_cache_time_header' => true,
    'cache_time_header_name' => 'X-ResponseCache-Cached-On',

    // Store de cache a usar para las respuestas. "file" porque el hosting
    // actual no tiene Redis; si en el futuro se suma Redis, cambiar solo
    // esta linea (o RESPONSE_CACHE_DRIVER en .env).
    'cache_store' => env('RESPONSE_CACHE_DRIVER', 'file'),

    // Headers que NO hay que guardar en la respuesta cacheada. Set-Cookie es
    // el critico: sin esto, la cookie de sesion/XSRF de la primera visita
    // que generó el cache se reenviaria a todo el resto de los visitantes.
    'remove_response_headers' => [
        'Set-Cookie',
    ],

];
