<?php

namespace App\Services;

use Throwable;

/**
 * Distingue un rechazo DEFINITIVO de esa dirección (no existe) de problemas pasajeros o del remitente.
 * Ante la duda devuelve false: nunca se marca como rebotada una dirección por un bloqueo de política,
 * un límite de envío o una caída, porque eso afectaría a toda la lista.
 */
class NewsletterBounceDetector
{
    public static function isHardBounce(Throwable $e): bool
    {
        $message = mb_strtolower($e->getMessage());

        // Tiene que ser un rechazo permanente de destinatario (550/551/553 o 5.1.x)...
        if (! preg_match('/\b(550|551|553)\b/', $message) && ! preg_match('/\b5\.1\.\d{1,3}\b/', $message)) {
            return false;
        }

        // ...que no sea de política, reputación o límites (esos NO son culpa de la dirección).
        if (preg_match('/\b554\b|\b5\.7\.\d{1,3}\b|\b4\d\d\b|spam|blocked|blacklist|denied|policy|reputation|rate limit|throttl|try again|greylist/', $message)) {
            return false;
        }

        return (bool) preg_match(
            '/user unknown|unknown user|no such user|does not exist|doesn.?t exist|mailbox (unavailable|not found|does not exist)|invalid (recipient|mailbox|address)|recipient (address )?(rejected|not found)|address rejected|account .{0,40}(disabled|does not exist)|\b5\.1\.[0-9]{1,3}\b/',
            $message
        );
    }
}
