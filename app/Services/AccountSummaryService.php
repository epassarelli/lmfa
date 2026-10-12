<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\EntityClaimRequest;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\News;
use App\Models\User;

/**
 * Resumen de la actividad de una persona en el portal (página "Mis datos"). Todo se calcula al
 * consultar: no se guarda nada.
 */
class AccountSummaryService
{
    public function __construct(private EntityClaimService $claims)
    {
    }

    /**
     * Fichas (artistas, festivales, ...) que la persona administra, con el enlace de edición.
     * Es el mismo listado que muestra Mis fichas.
     *
     * @return array<int, array{label:string, name:string, edit_url:string}>
     */
    public function managedEntities(User $user, int $limit = 50): array
    {
        $managed = [];

        foreach (config('entity_management.entities') as $config) {
            $column = $config['name_column'];

            foreach ($config['model']::query()->managedOrLegacyBy($user)->orderBy($column)->limit($limit)->get(['id', $column]) as $entity) {
                $managed[] = [
                    'label' => $config['label'],
                    'name' => (string) $entity->{$column},
                    'edit_url' => route($config['edit_route'], $entity->id),
                ];
            }
        }

        return $managed;
    }

    /**
     * Solicitudes propias por estado y solicitudes de otros que esta persona puede revisar.
     *
     * @return array{pending:int, approved:int, rejected:int, to_review:int}
     */
    public function claimCounts(User $user): array
    {
        $byStatus = EntityClaimRequest::query()
            ->where('user_id', $user->getKey())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'pending' => (int) ($byStatus[EntityClaimRequest::PENDING] ?? 0),
            'approved' => (int) ($byStatus[EntityClaimRequest::APPROVED] ?? 0),
            'rejected' => (int) ($byStatus[EntityClaimRequest::REJECTED] ?? 0),
            'to_review' => $this->claims->claimsToReview($user)->count(),
        ];
    }

    /**
     * Contenido creado por la persona: total, publicado y en revisión. Solo filas con datos.
     *
     * @return array<int, array{label:string, total:int, published:int, review:int}>
     */
    public function content(User $user): array
    {
        $sources = [
            ['Discos', Album::class, 'user_id', 'estado = 1'],
            ['Canciones', Cancion::class, 'user_id', 'estado = 1'],
            ['Artistas', Interprete::class, 'user_id', 'estado = 1'],
            ['Festivales', Festival::class, 'user_id', "status = 'published'"],
            ['Eventos', Event::class, 'created_by', "editorial_status = 'published'"],
            ['Noticias', News::class, 'created_by', "editorial_status = 'published'"],
        ];

        $rows = [];

        foreach ($sources as [$label, $model, $ownerColumn, $publishedCondition]) {
            $count = $model::query()
                ->where($ownerColumn, $user->getKey())
                ->toBase()
                ->selectRaw("COUNT(*) as total, COALESCE(SUM(CASE WHEN $publishedCondition THEN 1 ELSE 0 END), 0) as published")
                ->first();

            $total = (int) ($count->total ?? 0);

            if ($total > 0) {
                $published = (int) $count->published;
                $rows[] = ['label' => $label, 'total' => $total, 'published' => $published, 'review' => $total - $published];
            }
        }

        return $rows;
    }
}
