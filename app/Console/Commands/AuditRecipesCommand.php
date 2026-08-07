<?php

namespace App\Console\Commands;

use App\Models\Comida;
use App\Support\RecipeContent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuditRecipesCommand extends Command
{
    protected $signature = 'recipes:audit
        {--format=json : json, csv o both}
        {--path= : Ruta relativa bajo storage/app para exportar}
        {--limit= : Limitar cantidad analizada}
        {--dry-run : Analizar sin escribir archivos}';

    protected $description = 'Audita recetas historicas y exporta un inventario editorial sin modificar datos.';

    public function handle(): int
    {
        $format = Str::lower((string) $this->option('format'));
        $format = in_array($format, ['json', 'csv', 'both'], true) ? $format : 'json';
        $limit = $this->option('limit') ? max(1, (int) $this->option('limit')) : null;
        $dryRun = (bool) $this->option('dry-run');

        $query = Comida::query()->with('images')->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $recipes = $query->get();

        if ($recipes->isEmpty()) {
            $this->warn('No se encontraron recetas para analizar.');

            return self::SUCCESS;
        }

        $context = $this->buildContext($recipes);
        $inventory = $recipes->map(fn (Comida $recipe) => RecipeContent::audit($recipe, $context))->values();

        $basePath = $this->resolveBasePath();
        $timestamp = now()->format('Ymd_His');
        $baseFilename = 'recetas_auditoria_'.$timestamp;
        $written = [];

        if (! $dryRun) {
            if (in_array($format, ['json', 'both'], true)) {
                $jsonPath = $basePath.'/'.$baseFilename.'.json';
                Storage::disk('local')->put($jsonPath, json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $written[] = $jsonPath;
            }

            if (in_array($format, ['csv', 'both'], true)) {
                $csvPath = $basePath.'/'.$baseFilename.'.csv';
                Storage::disk('local')->put($csvPath, $this->toCsv($inventory->all()));
                $written[] = $csvPath;
            }
        }

        $this->info('Recetas analizadas: '.$inventory->count());
        $this->line('Acciones sugeridas:');
        foreach ($inventory->countBy('accion_editorial_sugerida')->sortKeys() as $action => $count) {
            $this->line(" - {$action}: {$count}");
        }

        $this->line('Prioridades sugeridas:');
        foreach ($inventory->countBy('prioridad_sugerida')->sortKeys() as $priority => $count) {
            $this->line(" - {$priority}: {$count}");
        }

        if ($dryRun) {
            $this->comment('Dry run activo: no se escribieron archivos.');
        } else {
            foreach ($written as $path) {
                $this->info('Exportado: storage/app/'.$path);
            }
        }

        return self::SUCCESS;
    }

    protected function buildContext($recipes): array
    {
        $duplicateSlugs = $recipes
            ->groupBy(fn (Comida $recipe) => trim((string) $recipe->slug))
            ->map->count()
            ->all();

        $duplicateTitles = $recipes
            ->groupBy(fn (Comida $recipe) => trim((string) $recipe->titulo))
            ->map->count()
            ->all();

        $contentDuplicates = [];
        foreach ($recipes as $recipe) {
            $hash = RecipeContent::contentHash(RecipeContent::visibleText($recipe->receta));
            $contentDuplicates[$hash][] = $recipe->id;
        }

        return [
            'duplicate_slugs' => $duplicateSlugs,
            'duplicate_titles' => $duplicateTitles,
            'duplicate_contents' => $contentDuplicates,
            'similar_titles' => $this->buildSimilarTitles($recipes),
        ];
    }

    protected function buildSimilarTitles($recipes): array
    {
        $normalized = $recipes->map(function (Comida $recipe) {
            return [
                'id' => $recipe->id,
                'title' => $recipe->titulo,
                'normalized' => Str::lower(Str::ascii((string) $recipe->titulo)),
            ];
        })->values();

        $similar = [];

        foreach ($normalized as $left) {
            foreach ($normalized as $right) {
                if ($left['id'] >= $right['id']) {
                    continue;
                }

                similar_text($left['normalized'], $right['normalized'], $percent);

                if ($percent >= 88.0) {
                    $similar[$left['id']][] = $right['title'];
                    $similar[$right['id']][] = $left['title'];
                }
            }
        }

        return $similar;
    }

    protected function toCsv(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $handle = fopen('php://temp', 'r+');
        $headers = array_keys($this->flattenForCsv($rows[0]));
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, array_values($this->flattenForCsv($row)));
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        return (string) $contents;
    }

    protected function flattenForCsv(array $row): array
    {
        $flattened = [];

        foreach ($row as $key => $value) {
            $flattened[$key] = is_array($value)
                ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : $value;
        }

        return $flattened;
    }

    protected function resolveBasePath(): string
    {
        $path = trim((string) $this->option('path'));

        if ($path === '') {
            return 'exports/recipes';
        }

        return trim(str_replace('\\', '/', $path), '/');
    }
}
