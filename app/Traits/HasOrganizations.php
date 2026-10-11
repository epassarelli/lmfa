<?php

namespace App\Traits;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Entidades del portal (Interprete, Festival, ...) administradas por organizaciones.
 */
trait HasOrganizations
{
    public function organizations(): MorphToMany
    {
        return $this->morphToMany(Organization::class, 'entity', 'organization_entities')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryOrganization(): ?Organization
    {
        return $this->organizations()->wherePivot('is_primary', true)->first();
    }

    /**
     * Entidades creadas por el usuario (acceso legacy por user_id) o administradas
     * por una organización activa en la que es miembro activo con rol de escritura.
     */
    public function scopeManagedOrLegacyBy(Builder $query, User $user): Builder
    {
        $model = $query->getModel();

        return $query->where(function (Builder $inner) use ($user, $model) {
            $inner->where($model->qualifyColumn('user_id'), $user->id)
                ->orWhereIn($model->getQualifiedKeyName(), $user->managedEntityIdsSubquery($model->getMorphClass()));
        });
    }
}
