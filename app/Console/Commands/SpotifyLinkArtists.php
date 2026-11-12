<?php

namespace App\Console\Commands;

use App\Models\Interprete;
use App\Services\Spotify\SpotifyArtistLinker;
use App\Services\Spotify\SpotifyClient;
use App\Services\Spotify\SpotifyException;
use Illuminate\Console\Command;

/** Spec: project/docs/specs/spotify-artistas-discos.md */
class SpotifyLinkArtists extends Command
{
    protected $signature = 'spotify:vincular-artistas {--dry-run : Muestra qué haría sin grabar} {--limit=40 : Cantidad máxima de artistas a revisar (0 = todos)} {--artist= : Solo este artista (ID del portal)} {--retry : Reintenta también los ya revisados sin éxito}';

    protected $description = 'Vincula con Spotify a los artistas activos sin vínculo (solo coincidencia exacta y única).';

    public function handle(SpotifyClient $client, SpotifyArtistLinker $linker): int
    {
        if (! $client->isConfigured()) {
            $this->error('Spotify no está configurado (SPOTIFY_CLIENT_ID / SPOTIFY_CLIENT_SECRET).');

            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $dry = (bool) $this->option('dry-run');

        $artists = Interprete::query()
            ->where('estado', 1)
            ->whereNull('spotify_artist_id')
            ->when($this->option('artist'), fn ($q, $id) => $q->where('id', (int) $id))
            ->when(! $this->option('retry'), fn ($q) => $q->whereNull('spotify_link_checked_at'))
            ->orderBy('id')
            ->when($limit > 0, fn ($q) => $q->limit($limit))
            ->get();

        $totals = [];
        $rows = [];

        foreach ($artists as $artist) {
            try {
                $result = $linker->autoLink($artist, ! $dry);
            } catch (SpotifyException $e) {
                $this->error('Corte por error de Spotify: '.$e->getMessage());

                return self::FAILURE;
            }

            $totals[$result['status']] = ($totals[$result['status']] ?? 0) + 1;
            $rows[] = [$artist->id, $artist->interprete, $result['status'], $result['detail']];
        }

        $this->table(['ID', 'Artista', 'Resultado', 'Detalle'], $rows);
        $this->info(($dry ? '[SIMULACIÓN] ' : '').'Revisados: '.count($artists).' · '.collect($totals)->map(fn ($n, $s) => "$s=$n")->implode(' · '));

        return self::SUCCESS;
    }
}
