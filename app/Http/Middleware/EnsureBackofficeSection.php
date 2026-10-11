<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Limita una sección del backoffice a quienes administran fichas que la habilitan
 * (ver config/backoffice_access.php). Uso: ->middleware('backoffice.section:discos').
 */
class EnsureBackofficeSection
{
    public const MESSAGE = 'Para gestionar este contenido necesitás que un administrador apruebe tu vínculo con una ficha. Podés solicitarlo desde Mis fichas.';

    public function handle(Request $request, Closure $next, string $section)
    {
        $user = $request->user();

        if (! $user || $user->canAccessBackofficeSection($section)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => self::MESSAGE], 403);
        }

        return redirect()->route('backend.my-entities.index')->with('warning', self::MESSAGE);
    }
}
