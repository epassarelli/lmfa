<?php

namespace App\Console\Commands;

use App\Models\PeniaProfile;
use App\Services\EditorialImageResolver;
use App\Support\SeoMetadata;
use Illuminate\Console\Command;

class AuditPeniaProfiles extends Command
{
    protected $signature = 'mfa:penias:audit
        {--published : Audita solo Peñas visibles/publicadas}
        {--csv= : Guarda el detalle completo en CSV sin modificar la base}
        {--limit=25 : Cantidad de Peñas con peor score que se muestran}';

    protected $description = 'Audita calidad editorial de Peñas en modo read-only y prioriza verificación, fuentes y datos operativos.';

    public function handle(EditorialImageResolver $imageResolver): int
    {
        $query = PeniaProfile::query()
            ->with(['images', 'provincia', 'locality'])
            ->withCount('events');

        if ($this->option('published')) {
            $query->publishedVisible();
        }

        $rows = [];
        $summary = [
            'total' => 0,
            'p1' => 0,
            'p2' => 0,
            'p3' => 0,
            'missing_location' => 0,
            'missing_contact' => 0,
            'missing_sources' => 0,
            'expired_verification' => 0,
            'missing_operational_details' => 0,
            'missing_seo' => 0,
            'fallback_image' => 0,
            'without_relations' => 0,
        ];

        $query->orderBy('id')->chunkById(200, function ($profiles) use ($imageResolver, &$rows, &$summary): void {
            foreach ($profiles as $profile) {
                $audit = $this->auditProfile($profile, $imageResolver);
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

        usort($rows, fn (array $a, array $b): int => ($a['score'] <=> $b['score'])
            ?: ($b['visits'] <=> $a['visits'])
            ?: ($a['id'] <=> $b['id'])
        );

        $this->table(
            ['Total', 'P1', 'P2', 'P3', 'Sin ubicación', 'Sin contacto', 'Sin fuentes', 'Verificación vencida', 'Sin datos operativos', 'Sin SEO', 'Fallback', 'Sin relaciones'],
            [[
                $summary['total'],
                $summary['p1'],
                $summary['p2'],
                $summary['p3'],
                $summary['missing_location'],
                $summary['missing_contact'],
                $summary['missing_sources'],
                $summary['expired_verification'],
                $summary['missing_operational_details'],
                $summary['missing_seo'],
                $summary['fallback_image'],
                $summary['without_relations'],
            ]]
        );

        $this->newLine();
        $this->info('Peñas con mayor deuda editorial');
        $this->table(
            ['ID', 'Peña', 'Score', 'Prioridad', 'Visitas', 'Palabras', 'Imagen', 'Faltantes'],
            array_map(fn (array $row): array => [
                $row['id'],
                $row['title'],
                $row['score'],
                $row['priority'],
                $row['visits'],
                $row['words'],
                $row['image_source'],
                implode(', ', $row['missing']),
            ], array_slice($rows, 0, max(1, (int) $this->option('limit'))))
        );

        $this->writeCsv($rows, trim((string) $this->option('csv')));

        $this->newLine();
        $this->info('Auditoría completada en modo read-only: no se modificó ninguna Peña.');

        return self::SUCCESS;
    }

    private function auditProfile(PeniaProfile $profile, EditorialImageResolver $imageResolver): array
    {
        $score = 0;
        $missing = [];
        $flags = [];

        if (filled($profile->title) && filled($profile->slug)) {
            $score += 10;
        } else {
            $missing[] = 'identidad';
        }

        if (filled($profile->excerpt)) {
            $score += 5;
        } else {
            $missing[] = 'bajada';
        }

        $body = SeoMetadata::clean($profile->body);
        $words = $body === '' ? 0 : count(preg_split('/\s+/u', $body, -1, PREG_SPLIT_NO_EMPTY) ?: []);
        if ($words >= 300) {
            $score += 10;
        } elseif ($words >= 150) {
            $score += 5;
            $missing[] = 'cuerpo<300';
        } else {
            $missing[] = 'cuerpo<150';
        }

        if ($profile->province_id && (filled($profile->city) || $profile->locality_id)) {
            $score += 15;
        } else {
            $missing[] = 'ubicación';
            $flags[] = 'missing_location';
        }

        if (collect([$profile->phone, $profile->email, $profile->website, $profile->reservation_url])->contains(fn ($value) => filled($value))) {
            $score += 10;
        } else {
            $missing[] = 'contacto/canal_oficial';
            $flags[] = 'missing_contact';
        }

        if (filled($profile->venue_type)) {
            $score += 5;
        } else {
            $missing[] = 'tipo_de_espacio';
        }

        if (blank($profile->accessibility_notes)
            && blank($profile->regular_events_summary)
            && blank($profile->admission_notes)) {
            $missing[] = 'datos_operativos';
            $flags[] = 'missing_operational_details';
        }

        if (! empty($profile->source_urls)) {
            $score += 10;
        } else {
            $missing[] = 'fuentes';
            $flags[] = 'missing_sources';
        }

        if ($profile->hasCurrentVerification()) {
            $score += 15;
        } else {
            $missing[] = 'verificación_vigente';
            $flags[] = 'expired_verification';
        }

        $hasSeoTitle = filled($profile->seo_title);
        $hasMetaDescription = filled($profile->meta_description);
        if ($hasSeoTitle) {
            $score += 5;
        } else {
            $missing[] = 'seo_title';
        }
        if ($hasMetaDescription) {
            $score += 5;
        } else {
            $missing[] = 'meta_description';
        }
        if (! $hasSeoTitle || ! $hasMetaDescription) {
            $flags[] = 'missing_seo';
        }

        $resolvedImage = $imageResolver->resolve($profile, false);
        if (! $resolvedImage->isFallback()) {
            $score += 5;
        } else {
            $missing[] = 'imagen';
            $flags[] = 'fallback_image';
        }

        if ($profile->events_count > 0) {
            $score += 5;
        } else {
            $missing[] = 'eventos_relacionados';
            $flags[] = 'without_relations';
        }

        $priority = match (true) {
            $score <= 50 => 'P1',
            $score <= 75 => 'P2',
            default => 'P3',
        };

        return [
            'id' => $profile->id,
            'title' => $profile->title,
            'slug' => $profile->slug,
            'score' => $score,
            'priority' => $priority,
            'visits' => (int) $profile->visits,
            'words' => $words,
            'province' => $profile->provincia?->nombre,
            'locality' => $profile->locality?->name,
            'venue_type' => $profile->venue_type,
            'verification_status' => $profile->verification_status,
            'last_verified_at' => $profile->last_verified_at?->toIso8601String(),
            'image_source' => $resolvedImage->sourceType,
            'events_count' => (int) $profile->events_count,
            'missing' => $missing,
            'flags' => array_values(array_unique($flags)),
        ];
    }

    private function writeCsv(array $rows, string $csvPath): void
    {
        if ($csvPath === '') {
            return;
        }

        $directory = dirname($csvPath);
        if ($directory !== '.' && ! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $csv = fopen($csvPath, 'wb');
        fputcsv($csv, ['id', 'title', 'slug', 'score', 'priority', 'visits', 'words', 'province', 'locality', 'venue_type', 'verification_status', 'last_verified_at', 'image_source', 'events_count', 'missing']);

        foreach ($rows as $row) {
            fputcsv($csv, [
                $row['id'], $row['title'], $row['slug'], $row['score'], $row['priority'],
                $row['visits'], $row['words'], $row['province'], $row['locality'],
                $row['venue_type'], $row['verification_status'], $row['last_verified_at'],
                $row['image_source'], $row['events_count'], implode('|', $row['missing']),
            ]);
        }

        fclose($csv);
        $this->line('Detalle CSV: '.$csvPath);
    }
}
