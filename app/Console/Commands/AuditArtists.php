<?php

namespace App\Console\Commands;

use App\Models\Interprete;
use App\Services\EditorialImageResolver;
use App\Support\SeoMetadata;
use Illuminate\Console\Command;

class AuditArtists extends Command
{
    protected $signature = 'mfa:artists:audit
        {--active : Audita sólo intérpretes activos}
        {--csv= : Guarda el detalle completo en CSV sin modificar la base}
        {--limit=25 : Cantidad de intérpretes con peor score que se muestran}';

    protected $description = 'Audita calidad editorial de Biografías en modo read-only y prioriza deuda legacy.';

    public function handle(EditorialImageResolver $imageResolver): int
    {
        $query = Interprete::query()
            ->with(['images'])
            ->withCount(['noticias', 'events', 'festivales', 'discos', 'canciones']);

        if ($this->option('active')) {
            $query->where('estado', 1);
        }

        $rows = [];
        $summary = [
            'total' => 0,
            'p1' => 0,
            'p2' => 0,
            'p3' => 0,
            'missing_type' => 0,
            'missing_excerpt' => 0,
            'short_bio' => 0,
            'missing_seo_title' => 0,
            'missing_meta_description' => 0,
            'fallback_image' => 0,
            'without_relations' => 0,
        ];

        $query->orderBy('id')->chunkById(200, function ($artists) use ($imageResolver, &$rows, &$summary): void {
            foreach ($artists as $artist) {
                $audit = $this->auditArtist($artist, $imageResolver);
                $rows[] = $audit;
                $summary['total']++;
                $summary[strtolower($audit['priority'])]++;

                foreach ($audit['flags'] as $flag) {
                    if (array_key_exists($flag, $summary)) {
                        $summary[$flag]++;
                    }
                }
            }
        });

        usort($rows, fn (array $a, array $b): int => $a['score'] <=> $b['score']);

        $this->table(
            ['Total', 'P1', 'P2', 'P3', 'Sin tipo', 'Sin bajada', 'Bio pobre', 'Sin SEO', 'Sin meta', 'Fallback', 'Sin relaciones'],
            [[
                $summary['total'],
                $summary['p1'],
                $summary['p2'],
                $summary['p3'],
                $summary['missing_type'],
                $summary['missing_excerpt'],
                $summary['short_bio'],
                $summary['missing_seo_title'],
                $summary['missing_meta_description'],
                $summary['fallback_image'],
                $summary['without_relations'],
            ]]
        );

        $limit = max(1, (int) $this->option('limit'));

        $this->newLine();
        $this->info('Biografías con mayor deuda editorial');

        $this->table(
            ['ID', 'Intérprete', 'Score', 'Prioridad', 'Palabras', 'Imagen', 'Faltantes'],
            array_map(fn (array $row): array => [
                $row['id'],
                $row['name'],
                $row['score'],
                $row['priority'],
                $row['words'],
                $row['image_source'],
                implode(', ', $row['missing']),
            ], array_slice($rows, 0, $limit))
        );

        $csvPath = trim((string) $this->option('csv'));

        if ($csvPath !== '') {
            $directory = dirname($csvPath);
            if ($directory !== '.' && ! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $csv = fopen($csvPath, 'wb');
            fputcsv($csv, [
                'id', 'interprete', 'slug', 'score', 'priority', 'words',
                'artist_type', 'seo_title', 'meta_description', 'image_source',
                'news_count', 'events_count', 'festivals_count', 'albums_count',
                'songs_count', 'missing',
            ]);

            foreach ($rows as $row) {
                fputcsv($csv, [
                    $row['id'],
                    $row['name'],
                    $row['slug'],
                    $row['score'],
                    $row['priority'],
                    $row['words'],
                    $row['artist_type'],
                    $row['seo_title'],
                    $row['meta_description'],
                    $row['image_source'],
                    $row['news_count'],
                    $row['events_count'],
                    $row['festivals_count'],
                    $row['albums_count'],
                    $row['songs_count'],
                    implode('|', $row['missing']),
                ]);
            }

            fclose($csv);
            $this->line('Detalle CSV: '.$csvPath);
        }

        $this->newLine();
        $this->info('Auditoría completada en modo read-only: no se modificó ningún intérprete.');

        return self::SUCCESS;
    }

    private function auditArtist(Interprete $artist, EditorialImageResolver $imageResolver): array
    {
        $score = 0;
        $missing = [];
        $flags = [];

        if (filled($artist->interprete) && filled($artist->slug)) {
            $score += 10;
        } else {
            $missing[] = 'identidad';
        }

        if (in_array($artist->artist_type, ['soloist', 'group'], true)) {
            $score += 5;
        } else {
            $missing[] = 'artist_type';
            $flags[] = 'missing_type';
        }

        if (filled($artist->excerpt)) {
            $score += 10;
        } else {
            $missing[] = 'bajada';
            $flags[] = 'missing_excerpt';
        }

        $body = SeoMetadata::clean($artist->biografia);
        $words = $body === '' ? 0 : count(preg_split('/\s+/u', $body, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        if ($words >= 300) {
            $score += 25;
        } elseif ($words >= 150) {
            $score += 12;
            $missing[] = 'biografia<300';
            $flags[] = 'short_bio';
        } else {
            $missing[] = 'biografia<150';
            $flags[] = 'short_bio';
        }

        if (filled($artist->seo_title)) {
            $score += 10;
        } else {
            $missing[] = 'seo_title';
            $flags[] = 'missing_seo_title';
        }

        if (filled($artist->meta_description)) {
            $score += 10;
        } else {
            $missing[] = 'meta_description';
            $flags[] = 'missing_meta_description';
        }

        $resolvedImage = $imageResolver->resolve($artist);
        if (! $resolvedImage->isFallback()) {
            $score += 10;
        } else {
            $missing[] = 'imagen';
            $flags[] = 'fallback_image';
        }

        $relations =
            (int) $artist->noticias_count +
            (int) $artist->events_count +
            (int) $artist->festivales_count +
            (int) $artist->discos_count +
            (int) $artist->canciones_count;

        if ($relations > 0) {
            $score += 15;
        } else {
            $missing[] = 'relaciones';
            $flags[] = 'without_relations';
        }

        $hasOfficialChannel = collect([
            $artist->web,
            $artist->instagram,
            $artist->facebook,
            $artist->youtube,
        ])->contains(fn ($value) => filled($value));

        if ($hasOfficialChannel) {
            $score += 5;
        } else {
            $missing[] = 'canal_oficial';
        }

        $priority = match (true) {
            $score <= 50 => 'P1',
            $score <= 75 => 'P2',
            default => 'P3',
        };

        return [
            'id' => $artist->id,
            'name' => $artist->interprete,
            'slug' => $artist->slug,
            'score' => $score,
            'priority' => $priority,
            'words' => $words,
            'artist_type' => $artist->artist_type,
            'seo_title' => $artist->seo_title,
            'meta_description' => $artist->meta_description,
            'image_source' => $resolvedImage->sourceType,
            'news_count' => (int) $artist->noticias_count,
            'events_count' => (int) $artist->events_count,
            'festivals_count' => (int) $artist->festivales_count,
            'albums_count' => (int) $artist->discos_count,
            'songs_count' => (int) $artist->canciones_count,
            'missing' => $missing,
            'flags' => array_values(array_unique($flags)),
        ];
    }
}
