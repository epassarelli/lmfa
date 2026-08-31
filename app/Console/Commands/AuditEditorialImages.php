<?php

namespace App\Console\Commands;

use App\Models\Album;
use App\Models\Comida;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\KnowledgeArticle;
use App\Models\Mito;
use App\Models\News;
use App\Services\EditorialImageResolver;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuditEditorialImages extends Command
{
    protected $signature = 'mfa:images:audit
        {--entity=* : Limita la auditoría a entidades: news,event,festival,knowledge,artist,album,recipe,myth}
        {--published : Audita sólo contenido visible/publicado cuando la entidad tiene un estado equivalente}
        {--csv= : Guarda el detalle por registro en un CSV sin modificar la base}';

    protected $description = 'Audita en modo read-only cómo resuelve imágenes editoriales MFA: propia, relacionada o fallback.';

    public function handle(EditorialImageResolver $resolver): int
    {
        $requested = collect($this->option('entity'))
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->filter()
            ->values();

        $specs = $this->specs();

        if ($requested->isNotEmpty()) {
            $unknown = $requested->diff(array_keys($specs));
            if ($unknown->isNotEmpty()) {
                $this->error('Entidades desconocidas: '.$unknown->join(', '));
                return self::INVALID;
            }

            $specs = array_intersect_key($specs, array_flip($requested->all()));
        }

        $csvPath = trim((string) $this->option('csv'));
        $csv = null;

        if ($csvPath !== '') {
            $directory = dirname($csvPath);
            if ($directory !== '.' && ! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $csv = fopen($csvPath, 'wb');
            fputcsv($csv, ['entity', 'id', 'label', 'source_type', 'source_entity', 'url_or_media_id']);
        }

        $summary = [];

        foreach ($specs as $name => $spec) {
            /** @var class-string<Model> $model */
            $model = $spec['model'];
            /** @var Builder $query */
            $query = $model::query()->with($spec['with']);

            if ($this->option('published') && isset($spec['published'])) {
                $query = ($spec['published'])($query);
            }

            $counts = [
                'own_media' => 0,
                'own_legacy' => 0,
                'related' => 0,
                'fallback' => 0,
                'total' => 0,
            ];

            $query->orderBy($model::query()->getModel()->getQualifiedKeyName())
                ->chunkById(250, function ($items) use ($resolver, $name, &$counts, $csv): void {
                    foreach ($items as $entity) {
                        $resolved = $resolver->resolve($entity);
                        $sourceType = $resolved->sourceType;

                        if (array_key_exists($sourceType, $counts)) {
                            $counts[$sourceType]++;
                        }

                        $counts['total']++;

                        if ($csv) {
                            fputcsv($csv, [
                                $name,
                                $entity->getKey(),
                                $this->labelFor($entity),
                                $sourceType,
                                $resolved->sourceEntity,
                                $resolved->media?->getKey() ?? $resolved->url,
                            ]);
                        }
                    }
                });

            $summary[] = [
                'Entidad' => $name,
                'Total' => $counts['total'],
                'Propia media' => $counts['own_media'],
                'Legacy propia' => $counts['own_legacy'],
                'Relacionada' => $counts['related'],
                'Fallback' => $counts['fallback'],
                '% fallback' => $counts['total'] > 0
                    ? number_format(($counts['fallback'] / $counts['total']) * 100, 1).' %'
                    : '0.0 %',
            ];
        }

        if ($csv) {
            fclose($csv);
        }

        $this->table(
            ['Entidad', 'Total', 'Propia media', 'Legacy propia', 'Relacionada', 'Fallback', '% fallback'],
            $summary
        );

        $this->newLine();
        $this->info('Auditoría completada en modo read-only: no se modificó ningún registro.');

        if ($csvPath !== '') {
            $this->line('Detalle CSV: '.$csvPath);
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{model: class-string<Model>, with: array<int, string>, published?: callable(Builder): Builder}>
     */
    private function specs(): array
    {
        return [
            'news' => [
                'model' => News::class,
                'with' => ['images', 'interprete.images', 'festivales.images'],
                'published' => fn (Builder $query): Builder => $query->publishedVisible(),
            ],
            'event' => [
                'model' => Event::class,
                'with' => ['images', 'interpretes.images', 'festivales.images'],
                'published' => fn (Builder $query): Builder => $query->publishedVisible(),
            ],
            'festival' => [
                'model' => Festival::class,
                'with' => ['images', 'interpretes.images', 'events.images'],
                'published' => fn (Builder $query): Builder => $query->publishedVisible(),
            ],
            'knowledge' => [
                'model' => KnowledgeArticle::class,
                'with' => ['images', 'interpretes.images', 'festivales.images', 'events.images'],
                'published' => fn (Builder $query): Builder => $query->visible(),
            ],
            'artist' => [
                'model' => Interprete::class,
                'with' => ['images'],
                'published' => fn (Builder $query): Builder => $query->where('estado', 1),
            ],
            'album' => [
                'model' => Album::class,
                'with' => ['images', 'interprete.images'],
                'published' => fn (Builder $query): Builder => $query->where('estado', 1),
            ],
            'recipe' => [
                'model' => Comida::class,
                'with' => ['images'],
                'published' => fn (Builder $query): Builder => $query->where('estado', 1),
            ],
            'myth' => [
                'model' => Mito::class,
                'with' => ['images'],
                'published' => fn (Builder $query): Builder => $query->where('estado', 1),
            ],
        ];
    }

    private function labelFor(Model $entity): string
    {
        return trim((string) (
            $entity->title
            ?? $entity->titulo
            ?? $entity->interprete
            ?? $entity->album
            ?? $entity->slug
            ?? $entity->getKey()
        ));
    }
}
