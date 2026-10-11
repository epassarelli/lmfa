<?php

namespace App\Services;

use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Crea (de forma idempotente) la organización principal de una entidad del portal.
 * Un solista es una organización de una sola entidad.
 */
class OrganizationProvisioner
{
    public function forEntity(Model $entity, ?User $owner = null, array $attributes = []): Organization
    {
        return DB::transaction(function () use ($entity, $owner, $attributes) {
            $link = OrganizationEntity::query()
                ->where('entity_type', $entity->getMorphClass())
                ->where('entity_id', $entity->getKey())
                ->where('is_primary', true)
                ->first();

            $organization = $link?->organization;

            if (! $organization) {
                $name = $this->entityName($entity);

                $organization = Organization::create(array_merge([
                    'type' => $this->organizationType($entity),
                    'name' => $name,
                    'slug' => $this->uniqueSlug($name),
                    'status' => 'active',
                    'created_by' => $owner?->getKey(),
                ], $attributes));

                OrganizationEntity::create([
                    'organization_id' => $organization->getKey(),
                    'entity_type' => $entity->getMorphClass(),
                    'entity_id' => $entity->getKey(),
                    'is_primary' => true,
                ]);
            }

            if ($owner) {
                OrganizationMember::firstOrCreate(
                    ['organization_id' => $organization->getKey(), 'user_id' => $owner->getKey()],
                    ['role' => 'owner', 'status' => 'active']
                );
                $owner->forgetManagedEntities();
            }

            return $organization;
        });
    }

    private function organizationType(Model $entity): string
    {
        return match (true) {
            $entity instanceof Interprete => 'artista',
            $entity instanceof Festival => 'festival',
            default => Str::lower(class_basename($entity)),
        };
    }

    private function entityName(Model $entity): string
    {
        return (string) ($entity->interprete ?? $entity->title ?? $entity->name ?? class_basename($entity).' #'.$entity->getKey());
    }

    public function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'organizacion';
        $slug = $base;
        $suffix = 2;

        while (Organization::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
