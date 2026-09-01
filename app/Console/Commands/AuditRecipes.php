<?php

namespace App\Console\Commands;

use App\Models\Comida;
use App\Services\EditorialImageResolver;
use App\Support\SeoMetadata;
use Illuminate\Console\Command;

class AuditRecipes extends Command
{
    protected $signature = 'mfa:recipes:audit
        {--active : Audita sólo recetas activas}
        {--csv= : Guarda el detalle completo en CSV sin modificar la base}
        {--limit=25 : Cantidad de recetas con peor score que se muestran}';

    protected $description = 'Audita calidad editorial de Recetas en modo read-only y prioriza deuda legacy.';

    public function handle(EditorialImageResolver $imageResolver): int
    {
        $query = Comida::query()->with('images');

        if ($this->option('active')) {
            $query->where('estado', 1);
        }

        $rows = [];
        $summary = [
            'total' => 0,
            'p1' => 0,
            'p2' => 0,
            'p3' => 0,
            'missing_excerpt' => 0,
            'short_body' => 0,
            'missing_ingredients' => 0,
            'missing_instructions' => 0,
            'missing_seo_title' => 0,
            'missing_meta_description' => 0,
            'fallback_image' => 0,
        ];

        $query->orderBy('id')->chunkById(200, function ($recipes) use ($imageResolver, &$rows, &$summary): void {
            foreach ($recipes as $recipe) {
                $audit = $this->auditRecipe($recipe, $imageResolver);
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
            ['Total', 'P1', 'P2', 'P3', 'Sin bajada', 'Cuerpo pobre', 'Sin ingredientes', 'Sin pasos', 'Sin SEO', 'Sin meta', 'Fallback'],
            [[
                $summary['total'],
                $summary['p1'],
                $summary['p2'],
                $summary['p3'],
                $summary['missing_excerpt'],
                $summary['short_body'],
                $summary['missing_ingredients'],
                $summary['missing_instructions'],
                $summary['missing_seo_title'],
                $summary['missing_meta_description'],
                $summary['fallback_image'],
            ]]
        );

        $this->newLine();
        $this->info('Recetas con mayor deuda editorial');

        $this->table(
            ['ID', 'Receta', 'Score', 'Prioridad', 'Visitas', 'Palabras', 'Ingredientes', 'Pasos', 'Imagen', 'Faltantes'],
            array_map(fn (array $row): array => [
                $row['id'],
                $row['title'],
                $row['score'],
                $row['priority'],
                $row['visits'],
                $row['words'],
                $row['ingredients_count'],
                $row['instructions_count'],
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
                'ingredients_count', 'instructions_count', 'region',
                'seo_title', 'meta_description', 'image_source', 'missing',
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
                    $row['ingredients_count'],
                    $row['instructions_count'],
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
        $this->info('Auditoría completada en modo read-only: no se modificó ninguna receta.');

        return self::SUCCESS;
    }

    private function auditRecipe(Comida $recipe, EditorialImageResolver $imageResolver): array
    {
        $score = 0;
        $missing = [];
        $flags = [];

        if (filled($recipe->titulo) && filled($recipe->slug)) {
            $score += 10;
        } else {
            $missing[] = 'identidad';
        }

        if (filled($recipe->excerpt)) {
            $score += 10;
        } else {
            $missing[] = 'bajada';
            $flags[] = 'missing_excerpt';
        }

        $body = SeoMetadata::clean($recipe->receta);
        $words = $body === '' ? 0 : count(preg_split('/\s+/u', $body, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        if ($words >= 300) {
            $score += 20;
        } elseif ($words >= 150) {
            $score += 10;
            $missing[] = 'cuerpo<300';
            $flags[] = 'short_body';
        } else {
            $missing[] = 'cuerpo<150';
            $flags[] = 'short_body';
        }

        $ingredients = is_array($recipe->ingredients) ? array_values(array_filter($recipe->ingredients)) : [];
        if (count($ingredients) >= 3) {
            $score += 15;
        } else {
            $missing[] = 'ingredientes';
            $flags[] = 'missing_ingredients';
        }

        $instructions = is_array($recipe->instructions) ? array_values(array_filter($recipe->instructions)) : [];
        if (count($instructions) >= 2) {
            $score += 15;
        } else {
            $missing[] = 'preparacion';
            $flags[] = 'missing_instructions';
        }

        if (filled($recipe->seo_title)) {
            $score += 10;
        } else {
            $missing[] = 'seo_title';
            $flags[] = 'missing_seo_title';
        }

        if (filled($recipe->meta_description)) {
            $score += 10;
        } else {
            $missing[] = 'meta_description';
            $flags[] = 'missing_meta_description';
        }

        $resolvedImage = $imageResolver->resolve($recipe);
        if (! $resolvedImage->isFallback()) {
            $score += 10;
        } else {
            $missing[] = 'imagen';
            $flags[] = 'fallback_image';
        }

        $priority = match (true) {
            $score <= 50 => 'P1',
            $score <= 75 => 'P2',
            default => 'P3',
        };

        return [
            'id' => $recipe->id,
            'title' => $recipe->titulo,
            'slug' => $recipe->slug,
            'score' => $score,
            'priority' => $priority,
            'visits' => (int) $recipe->visitas,
            'words' => $words,
            'ingredients_count' => count($ingredients),
            'instructions_count' => count($instructions),
            'region' => $recipe->region,
            'seo_title' => $recipe->seo_title,
            'meta_description' => $recipe->meta_description,
            'image_source' => $resolvedImage->sourceType,
            'missing' => $missing,
            'flags' => array_values(array_unique($flags)),
        ];
    }
}
