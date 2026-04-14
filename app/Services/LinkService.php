<?php

namespace App\Services;

use App\Models\Interprete;
use Illuminate\Support\Facades\Cache;

class LinkService
{
    /**
     * Auto-link artist names in the given text.
     *
     * @param string $text
     * @return string
     */
    public function autoLinkArtists($text)
    {
        if (empty($text)) {
            return $text;
        }

        $artists = Cache::remember('seo_artists_list', 3600, function () {
            return Interprete::where('estado', 1)
                ->select('interprete', 'slug')
                ->get()
                ->toArray();
        });

        // Sort by length descending to match longest names first (e.g., "Atahualpa Yupanqui" before "Atahualpa")
        usort($artists, function ($a, $b) {
            return strlen($b['interprete']) - strlen($a['interprete']);
        });

        foreach ($artists as $artist) {
            $name = $artist['interprete'];
            $url = route('artista.show', $artist['slug']);
            
            // Regex to match name ONLY if NOT inside <a> tag or other HTML tag attributes
            // This is a more robust negative lookahead/lookbehind approach
            // We search for the name but ensure it's not preceded by < or part of a tag
            // The word boundary \b is essential
            $pattern = '/(?!(?:[^<]+>|[^>]+<\/a>))\b' . preg_quote($name, '/') . '\b/iu';
            
            $text = preg_replace($pattern, '<a href="' . $url . '" class="hover:underline text-orange-700 font-medium">$0</a>', $text, 1);
            // Limit to 1 replacement per artist to avoid over-optimization (spammy look)
        }

        return $text;
    }
}
