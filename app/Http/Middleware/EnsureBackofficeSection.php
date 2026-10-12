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

    public const ROLE_ONLY_MESSAGE = 'Esta sección es para usuarios verificados y administradores. Si administrás una ficha, gestionás su contenido desde Mis fichas.';

    public function handle(Request $request, Closure $next, string $section)
    {
        $user = $request->user();

        if (! $user || $user->canAccessBackofficeSection($section)) {
            return $next($request);
        }

        $rule = config("backoffice_access.sections.$section");
        $roleOnly = is_array($rule) && ! array_is_list($rule) && empty($rule['entities']);
        $message = $roleOnly ? self::ROLE_ONLY_MESSAGE : self::MESSAGE;

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route('backend.my-entities.index')->with('warning', $message);
    }
}
