<?php

namespace App\Support;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeArticle;
use App\Models\News;
use Illuminate\Support\Str;

class SeoMetadata
{
    public static function home(): array
    {
        return [
            'title' => 'Mi Folklore Argentino | Tradiciones, musica y cultura popular',
            'description' => 'Portal editorial sobre folklore argentino con noticias, artistas, letras, discos, festivales y contenidos de consulta permanente.',
            'h1' => 'Mi Folklore Argentino',
        ];
    }

    public static function artist(Interprete $interprete): array
    {
        $name = static::fallback($interprete->interprete, 'Artista del folklore argentino');
        $bio = static::excerpt($interprete->biografia, 160);

        return [
            'title' => "Biografia de {$name} | Folklore Argentino",
            'description' => $bio !== '' ? $bio : "{$name}, artista del folklore argentino. Biografia, canciones, discos, noticias y shows relacionados.",
            'h1' => $name,
        ];
    }

    public static function biography(Interprete $interprete): array
    {
        $name = static::fallback($interprete->interprete, 'Artista del folklore argentino');
        $bio = static::excerpt($interprete->biografia, 160);

        return [
            'title' => "Biografia de {$name} | Folklore Argentino",
            'description' => $bio !== '' ? $bio : "Biografia de {$name}, artista del folklore argentino, con su trayectoria y contexto musical.",
            'h1' => "Biografia de {$name}",
        ];
    }

    public static function news(News $news): array
    {
        $title = static::preferredText($news->seo_title, $news->titulo, $news->title, 'Noticia del folklore argentino');
        $description = static::preferredDescription(
            $news->meta_description,
            $news->excerpt,
            $news->noticia,
            160,
            'Noticia del folklore argentino con contexto, protagonistas y cobertura editorial.'
        );

        return [
            'title' => $title,
            'description' => $description,
            'h1' => static::preferredText($news->titulo, $news->title, 'Noticia del folklore argentino'),
        ];
    }

    public static function event(Event $event): array
    {
        $name = static::preferredText($event->seo_title, $event->titulo, $event->title, 'Evento de folklore argentino');
        $h1 = static::preferredText($event->titulo, $event->title, 'Evento de folklore argentino');
        $description = static::preferredDescription(
            $event->meta_description,
            $event->excerpt,
            $event->detalles,
            160,
            "Evento de folklore argentino con fecha, lugar y artistas relacionados."
        );

        return [
            'title' => $name,
            'description' => $description,
            'h1' => $h1,
        ];
    }

    public static function festival(Festival $festival): array
    {
        $name = static::preferredText($festival->seo_title, $festival->title, 'Festival del folklore argentino');
        $description = static::preferredDescription(
            $festival->meta_description,
            $festival->excerpt,
            $festival->body,
            160,
            "Festival del folklore argentino con informacion general, historia y contexto del evento."
        );

        return [
            'title' => str_contains($name, 'Folklore Argentino') ? $name : "{$name} | Folklore Argentino",
            'description' => $description,
            'h1' => static::preferredText($festival->title, 'Festival del folklore argentino'),
        ];
    }

    public static function evergreen(KnowledgeArticle $article): array
    {
        $title = static::preferredText($article->seo_title, $article->title, 'Enciclopedia del folklore argentino');
        $h1 = static::preferredText($article->title, 'Enciclopedia del folklore argentino');
        $description = static::preferredDescription(
            $article->meta_description,
            $article->excerpt,
            $article->body,
            160,
            'Contenido de referencia sobre folklore argentino para consulta permanente.'
        );

        if ($title === $h1) {
            $title .= ' | Enciclopedia del folklore argentino';
        }

        return [
            'title' => $title,
            'description' => $description,
            'h1' => $h1,
        ];
    }

    public static function album(Album $album, ?Interprete $interprete = null): array
    {
        $artist = static::preferredText($interprete?->interprete, $album->interprete?->interprete, 'Folklore argentino');
        $albumName = static::preferredText($album->album, 'Disco del folklore argentino');
        $year = static::clean($album->anio);
        $yearChunk = $year !== '' ? " ({$year})" : '';

        return [
            'title' => "{$albumName}{$yearChunk} | {$artist}",
            'description' => "{$albumName}{$yearChunk} de {$artist}. Ficha del disco, canciones relacionadas y contexto dentro del folklore argentino.",
            'h1' => $albumName,
        ];
    }

    public static function song(Cancion $cancion, ?Interprete $interprete = null): array
    {
        $artist = static::preferredText($interprete?->interprete, $cancion->interprete?->interprete, 'Folklore argentino');
        $songName = static::preferredText($cancion->cancion, 'Letra del folklore argentino');
        $lyricsExcerpt = static::lyricsExcerpt($cancion->letra, 160);

        return [
            'title' => "Letra de {$songName} | {$artist}",
            'description' => $lyricsExcerpt !== '' ? $lyricsExcerpt : "Letra de {$songName} interpretada por {$artist}.",
            'h1' => $songName,
        ];
    }

    public static function clean(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $spacedHtml = preg_replace('/<\s*\/?(p|div|br|li|ul|ol|h[1-6])\b[^>]*>/i', ' ', $decoded);
        $withoutHtml = strip_tags((string) $spacedHtml);
        $normalized = preg_replace('/\s+/u', ' ', $withoutHtml);

        return trim((string) $normalized);
    }

    public static function excerpt(?string $value, int $maxLength = 160): string
    {
        $text = static::clean($value);

        if ($text === '' || Str::length($text) <= $maxLength) {
            return $text;
        }

        $sentences = preg_split('/(?<=[.!?])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $candidate = '';

        foreach ($sentences as $sentence) {
            $merged = trim($candidate === '' ? $sentence : "{$candidate} {$sentence}");

            if (Str::length($merged) > $maxLength) {
                break;
            }

            $candidate = $merged;
        }

        if ($candidate !== '' && Str::length($candidate) >= 110) {
            return $candidate;
        }

        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $truncated = '';

        foreach ($words as $word) {
            $merged = trim($truncated === '' ? $word : "{$truncated} {$word}");

            if (Str::length($merged) > $maxLength) {
                break;
            }

            $truncated = $merged;
        }

        return rtrim($truncated, ",;:- \t\n\r\0\x0B") . '.';
    }

    private static function preferredDescription(?string $preferred, ?string $secondary, ?string $fallbackSource, int $maxLength, string $default): string
    {
        foreach ([$preferred, $secondary, $fallbackSource] as $value) {
            $clean = static::clean($value);

            if ($clean !== '') {
                return static::excerpt($clean, $maxLength);
            }
        }

        return $default;
    }

    private static function lyricsExcerpt(?string $value, int $maxLength): string
    {
        if ($value === null) {
            return '';
        }

        $prepared = preg_replace('/<(br|\/p|\/div)\s*\/?>/i', "\n", $value);
        $lines = preg_split('/\R+/u', strip_tags(html_entity_decode((string) $prepared, ENT_QUOTES | ENT_HTML5, 'UTF-8'))) ?: [];
        $filtered = [];

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/u', ' ', $line));

            if ($line === '' || static::looksLikeChordLine($line)) {
                continue;
            }

            $filtered[] = $line;
        }

        return static::excerpt(implode(' ', $filtered), $maxLength);
    }

    private static function looksLikeChordLine(string $line): bool
    {
        $tokens = preg_split('/\s+/u', $line, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($tokens === []) {
            return false;
        }

        foreach ($tokens as $token) {
            if (! preg_match('/^[A-G](#|b)?([a-z0-9+\-\/º°]{0,6})$/iu', $token)) {
                return false;
            }
        }

        return true;
    }

    private static function preferredText(?string ...$values): string
    {
        foreach ($values as $value) {
            $clean = static::clean($value);

            if ($clean !== '') {
                return $clean;
            }
        }

        return '';
    }

    private static function fallback(?string $value, string $default): string
    {
        $clean = static::clean($value);

        return $clean !== '' ? $clean : $default;
    }
}
