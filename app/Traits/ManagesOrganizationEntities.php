<?php

namespace App\Traits;

use App\Models\Organization;
use App\Models\OrganizationMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Permisos de escritura sobre entidades del portal a partir de la pertenencia a organizaciones.
 */
trait ManagesOrganizationEntities
{
    /** @var array<string, array<int, int>> */
    protected array $managedEntityIdsMemo = [];

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_members')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    public function organizationMemberships()
    {
        return $this->hasMany(OrganizationMember::class);
    }

    /**
     * Subconsulta con los ids de las entidades de un tipo que el usuario administra
     * como miembro activo (owner/editor) de una organización activa.
     */
    public function managedEntityIdsSubquery(string $entityType): QueryBuilder
    {
        return DB::table('organization_entities as oe')
            ->join('organization_members as om', 'om.organization_id', '=', 'oe.organization_id')
            ->join('organizations as o', 'o.id', '=', 'oe.organization_id')
            ->where('oe.entity_type', $entityType)
            ->where('om.user_id', $this->getKey())
            ->where('om.status', 'active')
            ->whereIn('om.role', config('entity_management.writer_roles', ['owner', 'editor']))
            ->where('o.status', 'active')
            ->select('oe.entity_id');
    }

    /**
     * @return array<int, int>
     */
    public function managedEntityIds(string $entityType): array
    {
        return $this->managedEntityIdsMemo[$entityType] ??= $this->managedEntityIdsSubquery($entityType)
            ->pluck('oe.entity_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function forgetManagedEntities(): void
    {
        $this->managedEntityIdsMemo = [];
    }

    public function canManage(Model $entity): bool
    {
        if ($this->hasRole('administrador')) {
            return true;
        }

        if (isset($entity->user_id) && (int) $entity->user_id === (int) $this->getKey()) {
            return true;
        }

        return in_array((int) $entity->getKey(), $this->managedEntityIds($entity->getMorphClass()), true);
    }
}
