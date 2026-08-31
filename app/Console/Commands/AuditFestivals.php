<?php

namespace App\Console\Commands;

use App\Models\Festival;
use App\Services\EditorialImageResolver;
use App\Support\SeoMetadata;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class AuditFestivals extends Command
{
    protected $signature = 'mfa:festivals:audit
        {--published : Audita sólo festivales visibles/publicados}
        {--csv= : Guarda el detalle completo en CSV sin modificar la base}
        {--limit=20 : Cantidad de festivales con peor score que se muestran en consola}';

    protected $description = 'Audita calidad editorial de Festivales en modo read-only y prioriza faltantes de contenido, SEO, media y relaciones.';

    public function handle(EditorialImageResolver $imageResolver): int
    {
        $query = Festival::query()
            ->with([
                'images',
                'interpretes.images',
                'events.images',
                'provincia',
                'locality',
                'mes',
            ])
            ->withCount([
                'noticias',
                'events',
                'interpretes',
                'knowledgeArticles',
            ]);

        if ($this->option('published')) {
            $query->publishedVisible();
        }

        $rows = [];
        $summary = [
            'total' => 0,
            'p1' => 0,
            'p2' => 0,
            'p3' => 0,
            'missing_province' => 0,
            'missing_locality' => 0,
            'missing_month' => 0,
            'missing_seo_title' => 0,
            'missing_meta_description' => 0,
            'fallback_image' => 0,
            'without_relations' => 0,
        ];

        $query->orderBy('id')->chunkById(200, function ($festivals) use ($imageResolver, &$rows, &$summary): void {
            foreach ($festivals as $festival) {
                $audit = $this->auditFestival($festival, $imageResolver);
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
            ['Total', 'P1', 'P2', 'P3', 'Sin provincia', 'Sin localidad', 'Sin mes', 'Sin SEO title', 'Sin meta', 'Fallback', 'Sin relaciones'],
            [[
                $summary['total'],
                $summary['p1'],
                $summary['p2'],
                $summary['p3'],
                $summary['missing_province'],
                $summary['missing_locality'],
                $summary['missing_month'],
                $summary['missing_seo_title'],
                $summary['missing_meta_description'],
                $summary['fallback_image'],
                $summary['without_relations'],
            ]]
        );

        $limit = max(1, (int) $this->option('limit'));
        $worst = array_slice($rows, 0, $limit);

        $this->newLine();
        $this->info('Festivales con mayor deuda editorial');

        $this->table(
            ['ID', 'Festival', 'Score', 'Prioridad', 'Palabras', 'Imagen', 'Faltantes'],
            array_map(fn (array $row): array => [
                $row['id'],
                $row['title'],
                $row['score'],
                $row['priority'],
                $row['words'],
                $row['image_source'],
                implode(', ', $row['missing']),
            ], $worst)
        );

        $csvPath = trim((string) $this->option('csv'));

        if ($csvPath !== '') {
            $directory = dirname($csvPath);
            if ($directory !== '.' && ! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $csv = fopen($csvPath, 'wb');
            fputcsv($csv, [
                'id',
                'title',
                'slug',
                'score',
                'priority',
                'words',
                'province',
                'locality',
                'month',
                'seo_title',
                'meta_description',
                'image_source',
                'news_count',
                'events_count',
                'artists_count',
                'knowledge_count',
                'missing',
            ]);

            foreach ($rows as $row) {
                fputcsv($csv, [
                    $row['id'],
                    $row['title'],
                    $row['slug'],
                    $row['score'],
                    $row['priority'],
                    $row['words'],
                    $row['province'],
                    $row['locality'],
                    $row['month'],
                    $row['seo_title'],
                    $row['meta_description'],
                    $row['image_source'],
                    $row['news_count'],
                    $row['events_count'],
                    $row['artists_count'],
                    $row['knowledge_count'],
                    implode('|', $row['missing']),
                ]);
            }

            fclose($csv);
            $this->line('Detalle CSV: '.$csvPath);
        }

        $this->newLine();
        $this->info('Auditoría completada en modo read-only: no se modificó ningún Festival.');

        return self::SUCCESS;
    }

    private function auditFestival(Festival $festival, EditorialImageResolver $imageResolver): array
    {
        $score = 0;
        $missing = [];
        $flags = [];

        if (filled($festival->title) && filled($festival->slug)) {
            $score += 10;
        } else {
            $missing[] = 'identidad';
        }

        if (filled($festival->excerpt)) {
            $score += 10;
        } else {
            $missing[] = 'bajada';
        }

        $body = SeoMetadata::clean($festival->body);
        $words = $body === ''
            ? 0
            : count(preg_split('/\s+/u', $body, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        if ($words >= 300) {
            $score += 20;
        } elseif ($words >= 150) {
            $score += 10;
            $missing[] = 'cuerpo<300';
        } else {
            $missing[] = 'cuerpo<150';
        }

        if ($festival->province_id) {
            $score += 10;
        } else {
            $missing[] = 'provincia';
            $flags[] = 'missing_province';
        }

        if ($festival->locality_id) {
            $score += 5;
        } else {
            $missing[] = 'localidad';
            $flags[] = 'missing_locality';
        }

        if ($festival->mes_id) {
            $score += 10;
        } else {
            $missing[] = 'mes';
            $flags[] = 'missing_month';
        }

        if (filled($festival->seo_title)) {
            $score += 10;
        } else {
            $missing[] = 'seo_title';
            $flags[] = 'missing_seo_title';
        }

        if (filled($festival->meta_description)) {
            $score += 10;
        } else {
            $missing[] = 'meta_description';
            $flags[] = 'missing_meta_description';
        }

        $resolvedImage = $imageResolver->resolve($festival);
        if (! $resolvedImage->isFallback()) {
            $score += 10;
        } else {
            $missing[] = 'imagen_propia/relacionada';
            $flags[] = 'fallback_image';
        }

        $relationCount =
            (int) $festival->noticias_count +
            (int) $festival->events_count +
            (int) $festival->interpretes_count +
            (int) $festival->knowledge_articles_count;

        if ($relationCount > 0) {
            $score += 5;
        } else {
            $missing[] = 'relaciones';
            $flags[] = 'without_relations';
        }

        $priority = match (true) {
            $score <= 50 => 'P1',
            $score <= 75 => 'P2',
            default => 'P3',
        };

        return [
            'id' => $festival->id,
            'title' => $festival->title,
            'slug' => $festival->slug,
            'score' => $score,
            'priority' => $priority,
            'words' => $words,
            'province' => $festival->provincia?->nombre,
            'locality' => $festival->locality?->name,
            'month' => $festival->mes?->nombre,
            'seo_title' => $festival->seo_title,
            'meta_description' => $festival->meta_description,
            'image_source' => $resolvedImage->sourceType,
            'news_count' => (int) $festival->noticias_count,
            'events_count' => (int) $festival->events_count,
            'artists_count' => (int) $festival->interpretes_count,
            'knowledge_count' => (int) $festival->knowledge_articles_count,
            'missing' => $missing,
            'flags' => array_values(array_unique($flags)),
        ];
    }
}
