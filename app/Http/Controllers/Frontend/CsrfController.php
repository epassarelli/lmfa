<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Entrega un token CSRF fresco por AJAX.
 *
 * Existe porque algunas paginas publicas ahora se sirven desde el cache de
 * pagina completa (ver App\Support\ResponseCache\PublicPagesCacheProfile) y
 * un @csrf renderizado en el servidor quedaria "congelado" en esa copia
 * cacheada. Esta ruta queda deliberadamente afuera del cache (ver
 * PublicPagesCacheProfile::$excludedPatterns) para que cada visitante reciba
 * el token real de su propia sesion.
 */
class CsrfController extends Controller
{
    public function refresh(): JsonResponse
    {
        return response()->json([
            'token' => csrf_token(),
        ]);
    }
}
