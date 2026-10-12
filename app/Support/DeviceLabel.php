<?php

namespace App\Support;

/**
 * Descripción aproximada y legible de un dispositivo a partir del user agent ("Chrome en Windows").
 * Es solo orientativa: sirve para que una persona reconozca sus propias sesiones.
 */
class DeviceLabel
{
    public static function from(?string $userAgent): string
    {
        $ua = (string) $userAgent;

        $browser = match (true) {
            str_contains($ua, 'Edg/'), str_contains($ua, 'Edge/') => 'Edge',
            str_contains($ua, 'OPR/'), str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Firefox/'), str_contains($ua, 'FxiOS/') => 'Firefox',
            str_contains($ua, 'Chrome/'), str_contains($ua, 'CriOS/') => 'Chrome',
            str_contains($ua, 'Safari/') => 'Safari',
            default => 'Navegador',
        };

        // Android antes que Linux, e iOS antes que macOS: sus user agents contienen esas cadenas.
        $os = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone'), str_contains($ua, 'iPad'), str_contains($ua, 'iPod') => 'iOS',
            str_contains($ua, 'Mac OS X'), str_contains($ua, 'Macintosh') => 'macOS',
            str_contains($ua, 'Linux'), str_contains($ua, 'X11') => 'Linux',
            default => 'un dispositivo desconocido',
        };

        return "$browser en $os";
    }
}
