<?php

namespace App\Console\Commands;

use App\Models\Album;
use App\Models\Cancion;
use App\Services\EditorialImageResolver;
use Illuminate\Console\Command;

class AuditMusicCatalog extends Command
{
    protected $signature = 'mfa:music:audit
        {--active : Audita sólo registros activos}
        {--limit=25 : Cantidad de peores registros mostrados}';

    protected $description = 'Audita Discografía y Cancionero en modo read-only, incluyendo deuda legacy y estado de derechos.';

    public function handle(EditorialImageResolver $imageResolver): int
    {
        $albums = Album::query()
            ->with(['images', 'interprete.images'])
            ->withCount('canciones')
            ->when($this->option('active'), fn ($q) => $q->where('estado', 1))
            ->get();

        $songs = Cancion::query()
            ->with(['interprete', 'albunes'])
            ->when($this->option('active'), fn ($q) => $q->where('estado', 1))
            ->get();

        $albumRows = $albums->map(function (Album $album) use ($imageResolver) {
            $score = 0;
            $missing = [];

            if (filled($album->album) && filled($album->slug) && $album->interprete_id) {
                $score += 20;
            } else {
                $missing[] = 'identidad';
            }

            if (filled($album->anio)) $score += 10; else $missing[] = 'anio';
            if (filled($album->excerpt)) $score += 15; else $missing[] = 'excerpt';
            if (filled($album->album_type)) $score += 10; else $missing[] = 'album_type';
            if (filled($album->seo_title)) $score += 10; else $missing[] = 'seo_title';
            if (filled($album->meta_description)) $score += 10; else $missing[] = 'meta_description';
            if ($album->canciones_count > 0) $score += 15; else $missing[] = 'tracklist';

            $image = $imageResolver->resolve($album);
            if (! $image->isFallback()) $score += 10; else $missing[] = 'imagen';

            return [
                'type' => 'album',
                'id' => $album->id,
                'title' => $album->album,
                'score' => $score,
                'priority' => $score <= 50 ? 'P1' : ($score <= 75 ? 'P2' : 'P3'),
                'missing' => $missing,
            ];
        });

        $songRows = $songs->map(function (Cancion $song) {
            $score = 0;
            $missing = [];
            $lyrics = trim(strip_tags((string) $song->letra));
            $placeholder = in_array(mb_strtolower($lyrics), [
                'no disponible aun',
                'no disponible aún',
            ], true);

            if (filled($song->cancion) && filled($song->slug) && $song->interprete_id) {
                $score += 20;
            } else {
                $missing[] = 'identidad';
            }

            if (filled($song->excerpt)) $score += 15; else $missing[] = 'excerpt';
            if (filled($song->composer)) $score += 15; else $missing[] = 'composer';
            if ($song->albunes->isNotEmpty()) $score += 10; else $missing[] = 'album';
            if (filled($song->seo_title)) $score += 10; else $missing[] = 'seo_title';
            if (filled($song->meta_description)) $score += 10; else $missing[] = 'meta_description';

            if ($song->is_instrumental) {
                $score += 20;
            } elseif ($placeholder) {
                $missing[] = 'placeholder_letra';
            } elseif ($lyrics !== '') {
                if (in_array($song->rights_status, ['authorized', 'licensed', 'public_domain'], true)) {
                    $score += 20;
                } else {
                    $score += 5;
                    $missing[] = 'derechos_letra';
                }
            } else {
                $score += 10;
                if (blank($song->rights_status)) {
                    $missing[] = 'rights_status';
                }
            }

            return [
                'type' => 'song',
                'id' => $song->id,
                'title' => $song->cancion,
                'score' => $score,
                'priority' => $score <= 50 ? 'P1' : ($score <= 75 ? 'P2' : 'P3'),
                'missing' => $missing,
            ];
        });

        $rows = $albumRows->concat($songRows)->sortBy('score')->values();

        $this->table(
            ['Tipo', 'Total', 'P1', 'P2', 'P3'],
            [
                ['Álbum', $albumRows->count(), $albumRows->where('priority', 'P1')->count(), $albumRows->where('priority', 'P2')->count(), $albumRows->where('priority', 'P3')->count()],
                ['Canción', $songRows->count(), $songRows->where('priority', 'P1')->count(), $songRows->where('priority', 'P2')->count(), $songRows->where('priority', 'P3')->count()],
            ]
        );

        $this->newLine();
        $this->info('Registros con mayor deuda');

        $this->table(
            ['Tipo', 'ID', 'Título', 'Score', 'Prioridad', 'Faltantes'],
            $rows->take(max(1, (int) $this->option('limit')))
                ->map(fn ($row) => [
                    $row['type'],
                    $row['id'],
                    $row['title'],
                    $row['score'],
                    $row['priority'],
                    implode(', ', $row['missing']),
                ])
                ->all()
        );

        $placeholderCount = $songRows->filter(fn ($row) => in_array('placeholder_letra', $row['missing'], true))->count();
        $rightsDebt = $songRows->filter(fn ($row) => in_array('derechos_letra', $row['missing'], true))->count();

        $this->newLine();
        $this->line("Placeholders legacy de letra: {$placeholderCount}");
        $this->line("Letras existentes con derechos no verificados: {$rightsDebt}");
        $this->info('Auditoría completada en modo read-only.');

        return self::SUCCESS;
    }
}
