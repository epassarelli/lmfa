<?php

namespace App\Services\Publication;

class PublicationPayloadSanitizer
{
    public static function clean(mixed $value): mixed
    {
        if (is_array($value)) {
            $safe = [];
            foreach ($value as $key => $item) {
                if (preg_match('/token|authorization|cookie|password|secret|lease|refresh/i', (string) $key)) { continue; }
                $safe[$key] = self::clean($item);
            }
            return $safe;
        }
        if (!is_string($value)) { return $value; }
        $value = preg_replace('/\bEAA[A-Za-z0-9]+\b/', '[REDACTED]', $value);
        $value = preg_replace('/Bearer\s+[^\s"<>]+/i', 'Bearer [REDACTED]', $value);
        return preg_replace('/([?&](?:access_token|token|key|secret|lease|refresh_token)=)[^\s&#"<>]+/i', '$1[REDACTED]', $value);
    }
}
