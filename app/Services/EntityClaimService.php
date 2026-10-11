<?php

namespace App\Services;

use App\Models\EntityClaimRequest;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Solicitud y revisión de vínculos usuario <-> ficha (intérprete, festival, ...).
 */
class EntityClaimService
{
    public function __construct(private OrganizationProvisioner $provisioner)
    {
    }

    /**
     * @param  array{entity_type:string, entity_id:int, relationship:string, message:string, contact_phone?:?string}  $data
     */
    public function request(User $user, array $data): EntityClaimRequest
    {
        $config = config('entity_management.entities.'.$data['entity_type']);
        $entity = $config ? $config['model']::query()->find($data['entity_id']) : null;

        if (! $entity) {
            throw ValidationException::withMessages(['entity_id' => 'La ficha seleccionada no existe.']);
        }

        if ($user->canManage($entity)) {
            throw ValidationException::withMessages(['entity_id' => 'Ya administrás esta ficha.']);
        }

        $alreadyPending = EntityClaimRequest::query()
            ->pending()
            ->where('user_id', $user->getKey())
            ->where('entity_type', $entity->getMorphClass())
            ->where('entity_id', $entity->getKey())
            ->exists();

        if ($alreadyPending) {
            throw ValidationException::withMessages(['entity_id' => 'Ya tenés una solicitud pendiente para esta ficha.']);
        }

        $pendingCount = EntityClaimRequest::query()->pending()->where('user_id', $user->getKey())->count();
        $limit = (int) config('entity_management.claims.max_pending_per_user', 5);

        if ($pendingCount >= $limit) {
            throw ValidationException::withMessages(['entity_id' => "Llegaste al máximo de $limit solicitudes pendientes. Esperá la respuesta o cancelá alguna."]);
        }

        return EntityClaimRequest::create([
            'user_id' => $user->getKey(),
            'entity_type' => $entity->getMorphClass(),
            'entity_id' => $entity->getKey(),
            'relationship' => $data['relationship'],
            'message' => $data['message'],
            'contact_phone' => $data['contact_phone'] ?? null,
            'status' => EntityClaimRequest::PENDING,
        ]);
    }

    public function cancel(EntityClaimRequest $claim): EntityClaimRequest
    {
        $this->ensurePending($claim);
        $claim->update(['status' => EntityClaimRequest::CANCELLED]);

        return $claim;
    }

    public function approve(EntityClaimRequest $claim, User $reviewer, string $role, ?string $notes = null): EntityClaimRequest
    {
        $this->ensurePending($claim);

        $entity = $claim->entity;

        if (! $entity) {
            throw ValidationException::withMessages(['claim' => 'La ficha ya no existe.']);
        }

        return DB::transaction(function () use ($claim, $entity, $reviewer, $role, $notes) {
            $organization = $this->organizationFor($entity);

            OrganizationMember::query()->updateOrCreate(
                ['organization_id' => $organization->getKey(), 'user_id' => $claim->user_id],
                ['role' => $role, 'status' => 'active']
            );

            $claim->update([
                'status' => EntityClaimRequest::APPROVED,
                'organization_id' => $organization->getKey(),
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            return $claim;
        });
    }

    public function reject(EntityClaimRequest $claim, User $reviewer, ?string $notes = null): EntityClaimRequest
    {
        $this->ensurePending($claim);

        $claim->update([
            'status' => EntityClaimRequest::REJECTED,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        return $claim;
    }

    /**
     * Organización activa de la ficha; si no tiene ninguna se crea la principal.
     * Si la ficha solo tiene organizaciones inactivas la aprobación se rechaza,
     * porque un vínculo con una organización inactiva no otorgaría permisos.
     */
    private function organizationFor(Model $entity)
    {
        $organizations = $entity->organizations()->orderByDesc('organization_entities.is_primary')->orderBy('organizations.id')->get();

        if ($organizations->isEmpty()) {
            return $this->provisioner->forEntity($entity);
        }

        $active = $organizations->firstWhere('status', 'active');

        if (! $active) {
            throw ValidationException::withMessages([
                'claim' => 'La organización de esta ficha está inactiva. Activala (o vinculá otra) antes de aprobar.',
            ]);
        }

        return $active;
    }

    private function ensurePending(EntityClaimRequest $claim): void
    {
        if (! $claim->isPending()) {
            throw ValidationException::withMessages(['claim' => 'Esta solicitud ya fue resuelta.']);
        }
    }
}
