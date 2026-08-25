<?php

namespace App\Support;

class RichTextHeadingSanitizer
{
    public static function normalize(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        $html = preg_replace('/<\s*h1(\b[^>]*)>/i', '<h2$1>', $html);

        return preg_replace('/<\s*\/\s*h1\s*>/i', '</h2>', $html);
    }
}
