<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationRequest;
use App\Models\Organization;
use App\Models\Provincia;
use App\Services\OrganizationProvisioner;
use App\Support\BackendListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    public function __construct(private OrganizationProvisioner $provisioner)
    {
    }

    public function index(Request $request)
    {
        [$sort, $direction] = BackendListing::resolveSort(
            $request,
            ['id', 'name', 'type', 'status', 'entity_links_count', 'active_members_count', 'created_at'],
            'id'
        );

        $organizations = Organization::query()
            ->withCount([
                'entityLinks',
                'members as active_members_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->string('verified')->toString() === '1', fn ($query) => $query->where('is_verified', true))
            ->when($request->string('contactable')->toString() === '1', fn ($query) => $query->contactable())
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('backend.organizations.index', compact('organizations'));
    }

    public function create()
    {
        $organization = new Organization(['type' => 'artista', 'status' => 'active', 'source' => 'manual']);

        return view('backend.organizations.create', [
            'organization' => $organization,
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(OrganizationRequest $request)
    {
        $data = $this->payload($request, new Organization());
        $data['created_by'] = Auth::id();

        $organization = Organization::create($data);

        return redirect()->route('backend.organizations.edit', $organization)
            ->with('success', 'Organización creada. Ahora podés vincular fichas y miembros.');
    }

    public function edit(Organization $organization)
    {
        $organization->load([
            'entityLinks.entity',
            'members.user:id,name,email',
        ]);

        return view('backend.organizations.edit', [
            'organization' => $organization,
            'provincias' => Provincia::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function update(OrganizationRequest $request, Organization $organization)
    {
        $organization->update($this->payload($request, $organization));

        return redirect()->route('backend.organizations.edit', $organization)
            ->with('success', 'Organización actualizada.');
    }

    /**
     * Normaliza slug y banderas (consentimiento, baja, verificación) conservando la
     * fecha original si la bandera ya estaba activa.
     */
    private function payload(OrganizationRequest $request, Organization $organization): array
    {
        $data = $request->validated();

        $data['slug'] = filled($data['slug'] ?? null)
            ? $data['slug']
            : $this->provisioner->uniqueSlug($data['name'], $organization->exists ? $organization->id : null);

        $data['contact_consent_at'] = $request->boolean('contact_consent')
            ? ($organization->contact_consent_at ?? now())
            : null;
        $data['unsubscribed_at'] = $request->boolean('unsubscribed')
            ? ($organization->unsubscribed_at ?? now())
            : null;

        $verified = $request->boolean('is_verified');
        $data['is_verified'] = $verified;
        $data['verified_at'] = $verified ? ($organization->verified_at ?? now()) : null;
        $data['verified_by'] = $verified ? ($organization->verified_by ?? Auth::id()) : null;
        $data['verification_method'] = $verified ? ($data['verification_method'] ?? 'manual') : null;

        unset($data['contact_consent'], $data['unsubscribed']);

        return $data;
    }
}
