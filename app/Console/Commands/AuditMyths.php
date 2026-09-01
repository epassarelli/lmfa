<?php

namespace App\Console\Commands;

use App\Models\Mito;
use App\Services\EditorialImageResolver;
use App\Support\SeoMetadata;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AuditMyths extends Command
{
    protected $signature = 'mfa:myths:audit
        {--active : Audita sólo mitos activos}
        {--csv= : Guarda el detalle completo en CSV sin modificar la base}
        {--limit=25 : Cantidad de mitos con peor score que se muestran}';

    protected $description = 'Audita calidad editorial de Mitos y Leyendas en modo read-only y prioriza deuda legacy.';

    public function handle(EditorialImageResolver $imageResolver): int
    {
        $query = Mito::query()->with('images');

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
            'short_body' => 0,
            'missing_region' => 0,
            'missing_seo_title' => 0,
            'missing_meta_description' => 0,
            'fallback_image' => 0,
            'missing_sources' => 0,
        ];

        $query->orderBy('id')->chunkById(200, function ($myths) use ($imageResolver, &$rows, &$summary): void {
            foreach ($myths as $myth) {
                $audit = $this->auditMyth($myth, $imageResolver);
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

        usort($rows, fn (array $a, array $b): int =>
            ($a['score'] <=> $b['score'])
            ?: ($b['visits'] <=> $a['visits'])
            ?: ($a['id'] <=> $b['id'])
        );

        $this->table(
            ['Total', 'P1', 'P2', 'P3', 'Sin tipo', 'Sin bajada', 'Cuerpo pobre', 'Sin región', 'Sin SEO', 'Sin meta', 'Fallback', 'Sin fuentes'],
            [[
                $summary['total'],
                $summary['p1'],
                $summary['p2'],
                $summary['p3'],
                $summary['missing_type'],
                $summary['missing_excerpt'],
                $summary['short_body'],
                $summary['missing_region'],
                $summary['missing_seo_title'],
                $summary['missing_meta_description'],
                $summary['fallback_image'],
                $summary['missing_sources'],
            ]]
        );

        $this->newLine();
        $this->info('Mitos y leyendas con mayor deuda editorial');

        $this->table(
            ['ID', 'Título', 'Score', 'Prioridad', 'Visitas', 'Palabras', 'Tipo', 'Región', 'Imagen', 'Faltantes'],
            array_map(fn (array $row): array => [
                $row['id'],
                $row['title'],
                $row['score'],
                $row['priority'],
                $row['visits'],
                $row['words'],
                $row['content_type'] ?: '-',
                $row['region'] ?: '-',
                $row['image_source'],
                implode(', ', $row['missing']),
            ], array_slice($rows, 0, max(1, (int) $this->option('limit'))))
        );

        $csvPath = trim((string) $this->option('csv'));

        if ($csvPath !== '') {
            $directory = dirname($csvPath);
            if ($directory !== '.' && ! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $csv = fopen($csvPath, 'wb');
            fputcsv($csv, [
                'id', 'titulo', 'slug', 'score', 'priority', 'visits', 'words',
                'content_type', 'region', 'seo_title', 'meta_description',
                'image_source', 'missing',
            ]);

            foreach ($rows as $row) {
                fputcsv($csv, [
                    $row['id'],
                    $row['title'],
                    $row['slug'],
                    $row['score'],
                    $row['priority'],
                    $row['visits'],
                    $row['words'],
                    $row['content_type'],
                    $row['region'],
                    $row['seo_title'],
                    $row['meta_description'],
                    $row['image_source'],
                    implode('|', $row['missing']),
                ]);
            }

            fclose($csv);
            $this->line('Detalle CSV: '.$csvPath);
        }

        $this->newLine();
        $this->info('Auditoría completada en modo read-only: no se modificó ningún mito.');

        return self::SUCCESS;
    }

    private function auditMyth(Mito $myth, EditorialImageResolver $imageResolver): array
    {
        $score = 0;
        $missing = [];
        $flags = [];

        if (filled($myth->titulo) && filled($myth->slug)) {
            $score += 10;
        } else {
            $missing[] = 'identidad';
        }

        if (in_array($myth->content_type, ['myth', 'legend', 'urban_legend'], true)) {
            $score += 10;
        } else {
            $missing[] = 'content_type';
            $flags[] = 'missing_type';
        }

        if (filled($myth->excerpt)) {
            $score += 10;
        } else {
            $missing[] = 'bajada';
            $flags[] = 'missing_excerpt';
        }

        $body = SeoMetadata::clean($myth->mito);
        $words = $body === '' ? 0 : count(preg_split('/\s+/u', $body, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        if ($words >= 300) {
            $score += 25;
        } elseif ($words >= 150) {
            $score += 12;
            $missing[] = 'cuerpo<300';
            $flags[] = 'short_body';
        } else {
            $missing[] = 'cuerpo<150';
            $flags[] = 'short_body';
        }

        if (filled($myth->region)) {
            $score += 5;
        } else {
            $missing[] = 'region';
            $flags[] = 'missing_region';
        }

        if (filled($myth->seo_title)) {
            $score += 10;
        } else {
            $missing[] = 'seo_title';
            $flags[] = 'missing_seo_title';
        }

        if (filled($myth->meta_description)) {
            $score += 10;
        } else {
            $missing[] = 'meta_description';
            $flags[] = 'missing_meta_description';
        }

        $resolvedImage = $imageResolver->resolve($myth);
        if (! $resolvedImage->isFallback()) {
            $score += 10;
        } else {
            $missing[] = 'imagen';
            $flags[] = 'fallback_image';
        }

        $normalizedBody = Str::lower($body);
        if (str_contains($normalizedBody, 'fuentes consultadas') || str_contains($normalizedBody, 'fuentes y')) {
            $score += 10;
        } else {
            $missing[] = 'fuentes';
            $flags[] = 'missing_sources';
        }

        $priority = match (true) {
            $score <= 50 => 'P1',
            $score <= 75 => 'P2',
            default => 'P3',
        };

        return [
            'id' => $myth->id,
            'title' => $myth->titulo,
            'slug' => $myth->slug,
            'score' => $score,
            'priority' => $priority,
            'visits' => (int) $myth->visitas,
            'words' => $words,
            'content_type' => $myth->content_type,
            'region' => $myth->region,
            'seo_title' => $myth->seo_title,
            'meta_description' => $myth->meta_description,
            'image_source' => $resolvedImage->sourceType,
            'missing' => $missing,
            'flags' => array_values(array_unique($flags)),
        ];
    }
}
