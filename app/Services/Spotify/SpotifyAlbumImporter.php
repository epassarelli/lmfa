<?php

namespace App\Services\Spotify;

use App\Models\Album;
use App\Models\Interprete;
use App\Services\ImageSourceResolver;
use App\Services\ImageUploadService;
use App\Support\ImageSourceMetadata;
use Illuminate\Support\Str;
use Throwable;

/**
 * Propone discos de un artista vinculado a Spotify. Todo disco se crea INACTIVO (queda en Moderación).
 * Spec: project/docs/specs/spotify-artistas-discos.md
 */
class SpotifyAlbumImporter
{
    private const TYPES = ['album' => 'studio', 'single' => 'single', 'compilation' => 'compilation'];

    public function __construct(
        private SpotifyClient $client,
        private ImageSourceResolver $imageResolver,
        private ImageUploadService $imageService,
    ) {
    }

    /**
     * Discos de Spotify que el portal todavía no tiene.
     *
     * @return array{items: array<int, array<string, mixed>>, total_spotify: int, already_in_portal: int}
     */
    public function preview(Interprete $interprete): array
    {
        if (blank($interprete->spotify_artist_id)) {
            throw new SpotifyException('El artista no está vinculado a Spotify.');
        }

        $albums = $this->client->artistAlbums($interprete->spotify_artist_id);
        $existing = $interprete->discos()->get(['album', 'slug', 'spotify']);

        $haveIds = $existing->map(fn ($d) => preg_match('~embed/album/([A-Za-z0-9]+)~', (string) $d->spotify, $m) ? $m[1] : null)->filter()->all();
        $haveSlugs = $existing->pluck('slug')->filter()->all();
        $haveTitles = $existing->map(fn ($d) => SpotifyArtistLinker::normalize((string) $d->album))->all();

        $seen = [];
        $items = [];
        $already = 0;

        foreach ($albums as $al) {
            if (in_array($al['id'], $haveIds, true)) {
                $already++;

                continue;
            }

            $primary = $al['artists'][0]['id'] ?? null;
            $type = self::TYPES[$al['album_type'] ?? ''] ?? null;
            $year = substr((string) ($al['release_date'] ?? ''), 0, 4);

            if ($primary !== $interprete->spotify_artist_id || ! $type || blank($al['name'] ?? '') || ! preg_match('/^\d{4}$/', $year)) {
                continue;
            }

            $title = SpotifyArtistLinker::normalize($al['name']);
            $key = $title.'|'.$year;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $possibleDuplicate = in_array(Str::slug($al['name']), $haveSlugs, true) || in_array($title, $haveTitles, true);
            $images = collect($al['images'] ?? [])->sortByDesc('width')->values();

            $items[] = [
                'id' => $al['id'],
                'name' => $al['name'],
                'year' => $year,
                'release_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $al['release_date']) ? $al['release_date'] : null,
                'type' => $type,
                'type_label' => ['studio' => 'Disco', 'single' => 'Single', 'compilation' => 'Compilado'][$type],
                'cover' => $images->first()['url'] ?? null,
                'thumb' => ($images->firstWhere('width', 300) ?? $images->last())['url'] ?? null,
                'possible_duplicate' => $possibleDuplicate,
            ];
        }

        return ['items' => $items, 'total_spotify' => count($albums), 'already_in_portal' => $already];
    }

    /**
     * Crea (inactivos) los discos pedidos. Un fallo de portada no impide crear el disco.
     *
     * @param  array<int, string>  $spotifyIds
     * @return array{created: array<int, Album>, errors: array<int, string>, skipped: int}
     */
    public function import(Interprete $interprete, array $spotifyIds, int $userId, bool $skipPossibleDuplicates = false): array
    {
        $preview = collect($this->preview($interprete)['items'])->keyBy('id');
        $created = [];
        $errors = [];
        $skipped = 0;

        foreach (array_values(array_unique($spotifyIds)) as $id) {
            $item = $preview->get($id);

            if (! $item || ($skipPossibleDuplicates && $item['possible_duplicate'])) {
                $skipped++;

                continue;
            }

            try {
                $album = $this->createAlbum($interprete, $item, $userId);
                $created[] = $album;
            } catch (Throwable $e) {
                $errors[] = $item['name'].': '.$e->getMessage();

                continue;
            }

            if ($item['cover']) {
                try {
                    $this->attachCover($album, $item['cover']);
                } catch (Throwable $e) {
                    $errors[] = $item['name'].': disco creado sin portada ('.$e->getMessage().')';
                }
            }
        }

        $interprete->spotify_albums_checked_at = now();
        $interprete->save();

        return ['created' => $created, 'errors' => $errors, 'skipped' => $skipped];
    }

    private function createAlbum(Interprete $interprete, array $item, int $userId): Album
    {
        $album = new Album([
            'album' => $item['name'],
            'album_type' => $item['type'],
            'anio' => $item['year'],
            'release_date' => $item['release_date'],
            'spotify' => $this->embed($item['id']),
            'image_alt' => 'Portada de '.$item['name'].' de '.$interprete->interprete,
            'interprete_id' => $interprete->id,
            'slug' => $this->uniqueSlug($interprete, $item),
            'user_id' => $userId,
            'estado' => false,
            'visitas' => 0,
        ]);
        $album->save();

        return $album;
    }

    private function attachCover(Album $album, string $url): void
    {
        $file = null;

        try {
            $file = $this->imageResolver->resolve($url);
            $this->imageService->process(
                $file,
                $album,
                'album',
                'albunes',
                false,
                $album->slug,
                array_merge(ImageSourceMetadata::from($url), ['alt' => $album->image_alt ?: $album->album])
            );
        } finally {
            $this->imageResolver->cleanupTemporary($file);
        }
    }

    private function uniqueSlug(Interprete $interprete, array $item): string
    {
        $base = Str::slug($item['name']) ?: 'disco';
        $taken = fn (string $slug) => Album::where('interprete_id', $interprete->id)->where('slug', $slug)->exists();

        foreach ([$base, $base.'-'.$item['year'], $base.'-'.$item['year'].'-'.strtolower(substr($item['id'], 0, 6))] as $candidate) {
            if (! $taken($candidate)) {
                return $candidate;
            }
        }

        return $base.'-'.strtolower($item['id']);
    }

    private function embed(string $spotifyAlbumId): string
    {
        return '<iframe style="border-radius:12px" src="https://open.spotify.com/embed/album/'.$spotifyAlbumId.'?utm_source=generator" width="100%" height="380" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
    }
}
