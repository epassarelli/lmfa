<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationMember;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationMemberController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('organization_members', 'user_id')->where('organization_id', $organization->id),
            ],
            'role' => ['required', Rule::in(array_keys(config('entity_management.member_roles')))],
            'status' => ['required', Rule::in(array_keys(config('entity_management.member_statuses')))],
        ], [
            'user_id.unique' => 'El usuario ya es miembro de esta organización.',
        ], ['user_id' => 'usuario', 'role' => 'rol', 'status' => 'estado']);

        $organization->members()->create($data);

        return back()->with('success', 'Miembro agregado.');
    }

    public function update(Request $request, Organization $organization, OrganizationMember $member)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(config('entity_management.member_roles')))],
            'status' => ['required', Rule::in(array_keys(config('entity_management.member_statuses')))],
        ], [], ['role' => 'rol', 'status' => 'estado']);

        $member->update($data);

        return back()->with('success', 'Miembro actualizado.');
    }

    public function destroy(Organization $organization, OrganizationMember $member)
    {
        $member->delete();

        return back()->with('success', 'Miembro quitado.');
    }
}
