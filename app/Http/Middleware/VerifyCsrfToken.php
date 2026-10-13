<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'deleteuserdata',
        'newsletter/unsubscribe/*', // baja de un clic (RFC 8058); la autoriza el token de la URL
    ];
}
