<?php

namespace App\Services\Spotify;

use App\Models\Interprete;
use Illuminate\Support\Str;

/**
 * Vínculo artista del portal <-> artista de Spotify.
 * Spec: project/docs/specs/spotify-artistas-discos.md
 */
class SpotifyArtistLinker
{
    public const STATUS_LINKED = 'linked';
    public const STATUS_NO_MATCH = 'no_match';
    public const STATUS_AMBIGUOUS = 'ambiguous';
    public const STATUS_CONFLICT = 'conflict';
    public const STATUS_ALREADY = 'already_linked';

    public function __construct(private SpotifyClient $client)
    {
    }

    /** Minúsculas, sin tildes ni signos, espacios colapsados. */
    public static function normalize(string $text): string
    {
        $ascii = Str::ascii($text);

        return trim((string) preg_replace('/\s+/', ' ', (string) preg_replace('/[^a-z0-9 ]/', ' ', strtolower($ascii))));
    }

    /** Acepta ID, URL de artista o URI de Spotify. Devuelve null si no es válido. */
    public static function extractId(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (preg_match('~(?:open\.spotify\.com/(?:intl-[a-z]{2}/)?artist/|spotify:artist:)([A-Za-z0-9]{22})~', $input, $m)) {
            return $m[1];
        }

        return preg_match('/^[A-Za-z0-9]{22}$/', $input) ? $input : null;
    }

    /**
     * Candidatos de Spotify para un artista, marcando coincidencia exacta y si el ID ya está en uso.
     *
     * @return array<int, array<string, mixed>>
     */
    public function candidates(Interprete $interprete, bool $withAlbums = false): array
    {
        $needle = self::normalize($interprete->interprete);
        $results = $this->client->searchArtists($interprete->interprete, 10);
        $used = Interprete::whereIn('spotify_artist_id', array_column($results, 'id'))->pluck('interprete', 'spotify_artist_id');

        $mapped = array_map(fn (array $item) => [
            'id' => $item['id'],
            'name' => $item['name'] ?? '',
            'followers' => (int) ($item['followers']['total'] ?? 0),
            'genres' => array_slice($item['genres'] ?? [], 0, 4),
            'image' => $item['images'][1]['url'] ?? ($item['images'][0]['url'] ?? null),
            'url' => $item['external_urls']['spotify'] ?? 'https://open.spotify.com/artist/'.$item['id'],
            'exact' => self::normalize($item['name'] ?? '') === $needle,
            'used_by' => $used[$item['id']] ?? null,
            'albums' => [],
        ], $results);

        // Sin seguidores ni géneros (la API en modo desarrollo no los devuelve), lo que ayuda a distinguir homónimos
        // son los discos de cada perfil: se piden solo para las coincidencias exactas cuando hay más de una.
        if ($withAlbums && count(array_filter($mapped, fn ($c) => $c['exact'])) > 1) {
            foreach ($mapped as $i => $c) {
                if ($c['exact']) {
                    $mapped[$i]['albums'] = array_slice(array_values(array_unique(array_column($this->client->artistAlbums($c['id'], 1), 'name'))), 0, 5);
                }
            }
        }

        // Coincidencia exacta primero y, dentro de cada grupo, el perfil con más seguidores (suele ser el principal).
        usort($mapped, fn ($a, $b) => [$b['exact'], $b['followers']] <=> [$a['exact'], $a['followers']]);

        return $mapped;
    }

    /**
     * Vincula solo si hay UN resultado exacto y su ID no está en uso. No pisa vínculos existentes.
     *
     * @return array{status: string, spotify_id: ?string, detail: string}
     */
    public function autoLink(Interprete $interprete, bool $persist = true): array
    {
        if (filled($interprete->spotify_artist_id)) {
            return ['status' => self::STATUS_ALREADY, 'spotify_id' => $interprete->spotify_artist_id, 'detail' => 'ya vinculado'];
        }

        $exact = array_values(array_filter($this->candidates($interprete), fn ($c) => $c['exact']));

        if ($persist) {
            $interprete->spotify_link_checked_at = now();
        }

        if (count($exact) === 0) {
            $persist && $interprete->save();

            return ['status' => self::STATUS_NO_MATCH, 'spotify_id' => null, 'detail' => 'sin coincidencia exacta'];
        }

        if (count($exact) > 1) {
            $persist && $interprete->save();

            return ['status' => self::STATUS_AMBIGUOUS, 'spotify_id' => null, 'detail' => count($exact).' coincidencias exactas'];
        }

        if ($exact[0]['used_by']) {
            $persist && $interprete->save();

            return ['status' => self::STATUS_CONFLICT, 'spotify_id' => $exact[0]['id'], 'detail' => 'el ID ya está vinculado a '.$exact[0]['used_by']];
        }

        if ($persist) {
            $interprete->spotify_artist_id = $exact[0]['id'];
            $interprete->save();
        }

        return ['status' => self::STATUS_LINKED, 'spotify_id' => $exact[0]['id'], 'detail' => $exact[0]['name']];
    }

    /** Vínculo manual: valida formato, existencia en Spotify y que no lo use otro artista. */
    public function link(Interprete $interprete, string $input): void
    {
        $id = self::extractId($input);

        if (! $id) {
            throw new SpotifyException('El enlace o ID de Spotify no es válido. Pegá la URL del artista (open.spotify.com/artist/…).');
        }

        $owner = Interprete::where('spotify_artist_id', $id)->where('id', '!=', $interprete->id)->first();
        if ($owner) {
            throw new SpotifyException('Ese artista de Spotify ya está vinculado a «'.$owner->interprete.'».');
        }

        if ($this->client->artist($id) === null) {
            throw new SpotifyException('Spotify no encontró un artista con ese ID.');
        }

        $interprete->spotify_artist_id = $id;
        $interprete->spotify_link_checked_at = now();
        $interprete->save();
    }

    public function unlink(Interprete $interprete): void
    {
        $interprete->spotify_artist_id = null;
        $interprete->spotify_albums_checked_at = null;
        $interprete->save();
    }
}
