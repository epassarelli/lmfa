<?php

namespace App\Support;

/**
 * Avatar de iniciales generado como SVG en un data URI: sin subida de archivos, sin almacenamiento y
 * sin servicios externos. El color es estable por usuario (según un valor semilla, p. ej. su id).
 */
class InitialsAvatar
{
    /** Colores oscuros con contraste suficiente para texto blanco. */
    private const COLORS = ['#1d4ed8', '#0f766e', '#b45309', '#9d174d', '#6d28d9', '#b91c1c', '#15803d', '#334155'];

    public static function initials(string $name): string
    {
        $words = array_values(array_filter(array_map(
            fn (string $word) => preg_replace('/[^\p{L}\p{N}]/u', '', $word),
            preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: []
        ), fn ($word) => $word !== ''));

        if ($words === []) {
            return '?';
        }

        $initials = count($words) >= 2
            ? mb_substr($words[0], 0, 1).mb_substr($words[count($words) - 1], 0, 1)
            : mb_substr($words[0], 0, 2);

        return mb_strtoupper($initials);
    }

    public static function color(int|string $seed): string
    {
        return self::COLORS[crc32((string) $seed) % count(self::COLORS)];
    }

    public static function svg(string $name, int|string $seed = 0): string
    {
        $initials = htmlspecialchars(self::initials($name), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="128" height="128">'
            .'<rect width="128" height="128" fill="'.self::color($seed).'"/>'
            .'<text x="64" y="64" dy="0.35em" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="52" font-weight="600" fill="#ffffff">'.$initials.'</text>'
            .'</svg>';
    }

    public static function dataUri(string $name, int|string $seed = 0): string
    {
        return 'data:image/svg+xml;charset=UTF-8,'.rawurlencode(self::svg($name, $seed));
    }
}
