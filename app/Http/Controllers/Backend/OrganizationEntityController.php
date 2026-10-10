<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrganizationEntityController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $types = config('entity_management.entities');

        $data = $request->validate([
            'entity_type' => ['required', Rule::in(array_keys($types))],
            'entity_id' => ['required', 'integer', 'min:1'],
            'is_primary' => ['nullable', 'boolean'],
        ], [], ['entity_type' => 'tipo de ficha', 'entity_id' => 'ficha']);

        $modelClass = $types[$data['entity_type']]['model'];
        $entity = $modelClass::query()->find($data['entity_id']);

        if (! $entity) {
            return back()->withErrors(['entity_id' => 'La ficha seleccionada no existe.'])->withInput();
        }

        $link = OrganizationEntity::firstOrCreate([
            'organization_id' => $organization->id,
            'entity_type' => $entity->getMorphClass(),
            'entity_id' => $entity->getKey(),
        ], ['is_primary' => false]);

        if ($request->boolean('is_primary')) {
            $this->makePrimary($link);
        }

        return back()->with('success', $link->wasRecentlyCreated ? 'Ficha vinculada.' : 'La ficha ya estaba vinculada.');
    }

    public function primary(Organization $organization, OrganizationEntity $entityLink)
    {
        $this->makePrimary($entityLink);

        return back()->with('success', 'Organización principal actualizada para la ficha.');
    }

    public function destroy(Organization $organization, OrganizationEntity $entityLink)
    {
        $entityLink->delete();

        return back()->with('success', 'Vínculo quitado.');
    }

    /**
     * Una ficha tiene una sola organización principal.
     */
    private function makePrimary(OrganizationEntity $link): void
    {
        DB::transaction(function () use ($link) {
            OrganizationEntity::query()
                ->where('entity_type', $link->entity_type)
                ->where('entity_id', $link->entity_id)
                ->where('id', '!=', $link->id)
                ->update(['is_primary' => false]);

            $link->update(['is_primary' => true]);
        });
    }
}
