<?php

namespace App\Services\Spotify;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Cliente mínimo de la API web de Spotify (flujo client credentials, solo lectura de catálogo).
 * Spec: project/docs/specs/spotify-artistas-discos.md
 */
class SpotifyClient
{
    private const API = 'https://api.spotify.com/v1';
    private const TOKEN_URL = 'https://accounts.spotify.com/api/token';
    private const TOKEN_CACHE_KEY = 'spotify:client-credentials-token';

    public function isConfigured(): bool
    {
        return filled(config('spotify.client_id')) && filled(config('spotify.client_secret'));
    }

    /** @return array<int, array<string, mixed>> */
    public function searchArtists(string $name, int $limit = 10): array
    {
        $data = $this->get('/search', ['q' => $name, 'type' => 'artist', 'limit' => min($limit, 10)]);

        return $data['artists']['items'] ?? [];
    }

    /** @return array<string, mixed>|null */
    public function artist(string $id): ?array
    {
        try {
            return $this->get('/artists/'.$id);
        } catch (SpotifyException $e) {
            if ($e->getCode() === 404 || $e->getCode() === 400) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Discos del artista (álbumes, singles y compilados), paginados hasta agotarlos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function artistAlbums(string $artistId, int $maxPages = 12): array
    {
        $items = [];
        $path = '/artists/'.$artistId.'/albums';
        $query = ['include_groups' => 'album,single,compilation', 'limit' => 10];

        for ($page = 0; $page < $maxPages; $page++) {
            $data = $this->get($path, $query);
            $items = array_merge($items, $data['items'] ?? []);

            if (empty($data['next'])) {
                break;
            }

            $query['offset'] = ($query['offset'] ?? 0) + 10;
        }

        return $items;
    }

    /** @return array<string, mixed> */
    private function get(string $path, array $query = []): array
    {
        $response = $this->send(fn (PendingRequest $http) => $http->get(self::API.$path, $query));

        return $response->json() ?? [];
    }

    private function send(callable $call, bool $retriedAuth = false): Response
    {
        $response = null;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $response = $call(Http::timeout(15)->acceptJson()->withToken($this->token()));

            if ($response->status() === 429) {
                $wait = min(max((int) $response->header('Retry-After'), 1), 10);
                sleep($wait);

                continue;
            }

            break;
        }

        if ($response->status() === 401 && ! $retriedAuth) {
            Cache::forget(self::TOKEN_CACHE_KEY);

            return $this->send($call, true);
        }

        if (! $response->successful()) {
            throw new SpotifyException($this->messageFor($response), $response->status());
        }

        return $response;
    }

    private function token(): string
    {
        if (! $this->isConfigured()) {
            throw new SpotifyException('Spotify no está configurado (faltan SPOTIFY_CLIENT_ID y SPOTIFY_CLIENT_SECRET).');
        }

        $cached = Cache::get(self::TOKEN_CACHE_KEY);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::timeout(15)
            ->asForm()
            ->withBasicAuth((string) config('spotify.client_id'), (string) config('spotify.client_secret'))
            ->post(self::TOKEN_URL, ['grant_type' => 'client_credentials']);

        if (! $response->successful() || blank($response->json('access_token'))) {
            throw new SpotifyException('No se pudo obtener el token de Spotify ('.$response->status().'). Revisá las credenciales.', $response->status());
        }

        $token = (string) $response->json('access_token');
        Cache::put(self::TOKEN_CACHE_KEY, $token, max(60, (int) $response->json('expires_in', 3600) - 120));

        return $token;
    }

    private function messageFor(Response $response): string
    {
        return match ($response->status()) {
            403 => 'Spotify rechazó la consulta (403). Verificá que el Premium del dueño de la app siga activo.',
            429 => 'Spotify limitó las consultas (429). Probá de nuevo en unos minutos.',
            default => 'Error de Spotify ('.$response->status().').',
        };
    }
}
