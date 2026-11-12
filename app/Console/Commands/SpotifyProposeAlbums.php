<?php

namespace App\Console\Commands;

use App\Models\Interprete;
use App\Models\User;
use App\Services\Spotify\SpotifyAlbumImporter;
use App\Services\Spotify\SpotifyClient;
use App\Services\Spotify\SpotifyException;
use Illuminate\Console\Command;

/** Spec: project/docs/specs/spotify-artistas-discos.md */
class SpotifyProposeAlbums extends Command
{
    protected $signature = 'spotify:proponer-discos {--artist= : ID del artista del portal (si no, rota entre los vinculados)} {--max=15 : Máximo de discos por corrida} {--dry-run : Muestra qué crearía sin grabar} {--user= : ID del usuario al que se atribuyen los discos}';

    protected $description = 'Propone (inactivos) los discos nuevos de UN artista vinculado a Spotify.';

    public function handle(SpotifyClient $client, SpotifyAlbumImporter $importer): int
    {
        if (! $client->isConfigured()) {
            $this->error('RESULTADO=ERROR Spotify no está configurado.');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $max = max(1, (int) $this->option('max'));
        $userId = (int) ($this->option('user') ?: config('spotify.proposals_user_id'));

        if (! $dry && ! ($userId && User::whereKey($userId)->exists())) {
            $this->error('RESULTADO=ERROR Falta un usuario válido (--user o SPOTIFY_PROPOSALS_USER_ID).');

            return self::FAILURE;
        }

        $artist = $this->option('artist')
            ? Interprete::whereNotNull('spotify_artist_id')->find((int) $this->option('artist'))
            : Interprete::where('estado', 1)->whereNotNull('spotify_artist_id')
                ->orderByRaw('spotify_albums_checked_at IS NOT NULL')
                ->orderBy('spotify_albums_checked_at')
                ->orderBy('id')
                ->first();

        if (! $artist) {
            $this->warn('RESULTADO=SIN_ARTISTA No hay artistas vinculados a Spotify para procesar.');

            return self::SUCCESS;
        }

        $this->line("ARTISTA={$artist->interprete} (id {$artist->id})");

        try {
            $preview = $importer->preview($artist);
            $todo = array_values(array_filter($preview['items'], fn ($i) => ! $i['possible_duplicate']));
            $batch = array_slice($todo, 0, $max);

            $this->line('SPOTIFY: '.$preview['total_spotify'].' discos | YA EN PORTAL: '.$preview['already_in_portal'].' | NUEVOS: '.count($todo).' | ESTA CORRIDA: '.count($batch));

            if ($dry) {
                foreach ($batch as $item) {
                    $this->line("  (simulación) {$item['name']} | {$item['year']} | {$item['type']}");
                }
                $this->info('RESULTADO=SIMULACION artista='.$artist->interprete.' crearia='.count($batch));

                return self::SUCCESS;
            }

            $result = $importer->import($artist, array_column($batch, 'id'), $userId, true);
        } catch (SpotifyException $e) {
            $this->error('RESULTADO=ERROR '.$e->getMessage());

            return self::FAILURE;
        }

        $remaining = count($todo) - count($batch);
        if ($remaining > 0) {
            // Queda primero en la rotación para terminar en la próxima corrida.
            $artist->spotify_albums_checked_at = null;
            $artist->save();
        }

        foreach ($result['created'] as $album) {
            $this->line("  + {$album->album} ({$album->anio}) id={$album->id}");
        }
        foreach ($result['errors'] as $error) {
            $this->line("  ! {$error}");
        }

        $this->info('RESULTADO=OK artista='.$artist->interprete.' creados='.count($result['created']).' avisos='.count($result['errors']).' pendientes_para_proxima='.$remaining);

        return self::SUCCESS;
    }
}
