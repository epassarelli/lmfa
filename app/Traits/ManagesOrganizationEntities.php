<?php

namespace App\Traits;

use App\Models\Interprete;
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

    /** @var array<string, bool> */
    protected array $managedEntityTypeMemo = [];

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
        $this->managedEntityTypeMemo = [];
    }

    /**
     * Administra al menos una ficha del tipo (clave de config/entity_management.php), ya sea por
     * membresía o por ser el creador legacy (mismo criterio que "Mis fichas").
     */
    public function hasManagedEntityOfType(string $typeKey): bool
    {
        if (array_key_exists($typeKey, $this->managedEntityTypeMemo)) {
            return $this->managedEntityTypeMemo[$typeKey];
        }

        $model = config("entity_management.entities.$typeKey.model");

        return $this->managedEntityTypeMemo[$typeKey] = $model
            ? $model::query()->managedOrLegacyBy($this)->exists()
            : false;
    }

    /**
     * Acceso a una sección del backoffice según las fichas que administra el usuario.
     * Con el flag apagado, para administradores o para secciones no restringidas siempre es true.
     */
    public function canAccessBackofficeSection(string $section): bool
    {
        if (! config('backoffice_access.enabled') || $this->hasRole('administrador')) {
            return true;
        }

        $types = config("backoffice_access.sections.$section");

        if ($types === null) {
            return true;
        }

        foreach ($types as $typeKey) {
            if ($this->hasManagedEntityOfType($typeKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * El usuario no administra ninguna ficha de los tipos registrados.
     */
    public function hasNoManagedEntities(): bool
    {
        foreach (array_keys(config('entity_management.entities', [])) as $typeKey) {
            if ($this->hasManagedEntityOfType($typeKey)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ids de los intérpretes que el usuario administra como miembro owner/editor.
     *
     * @return array<int, int>
     */
    public function managedInterpreteIds(): array
    {
        return $this->managedEntityIds((new Interprete)->getMorphClass());
    }

    public function managedInterpreteIdsSubquery(): QueryBuilder
    {
        return $this->managedEntityIdsSubquery((new Interprete)->getMorphClass());
    }

    /**
     * Administra el intérprete por membresía (o es administrador global).
     * No considera el user_id legacy: ese acceso aplica solo a la ficha, no a su contenido.
     */
    public function canManageInterpreteId(?int $interpreteId): bool
    {
        if ($this->hasRole('administrador')) {
            return true;
        }

        return $interpreteId !== null && in_array($interpreteId, $this->managedInterpreteIds(), true);
    }

    /**
     * Usuario sin permiso global de escritura sobre un contenido (p. ej. 'update album'):
     * su alcance queda limitado a los intérpretes que administra por membresía.
     */
    public function isScopedToManagedInterpretes(string $globalPermission): bool
    {
        return ! $this->hasRole('administrador') && ! $this->can($globalPermission);
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
