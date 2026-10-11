<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationRequest;
use App\Models\Organization;
use App\Models\Provincia;
use App\Services\OrganizationProvisioner;
use App\Support\BackendListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrganizationController extends Controller
{
    public function __construct(private OrganizationProvisioner $provisioner)
    {
    }

    public function index(Request $request)
    {
        [$sort, $direction] = BackendListing::resolveSort(
            $request,
            ['id', 'name', 'type', 'status', 'contact_stage', 'last_contacted_at', 'entity_links_count', 'active_members_count', 'created_at'],
            'id'
        );

        $organizations = $this->filtered($request)
            ->withCount([
                'entityLinks',
                'members as active_members_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('backend.organizations.index', compact('organizations'));
    }

    /**
     * CSV de organizaciones contactables: activas, con consentimiento, sin baja y con email.
     * Respeta la búsqueda y el tipo del listado, pero nunca incluye a quien no se puede contactar.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request->duplicate($request->only(['search', 'type'])))
            ->contactable()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('name');

        $filename = 'organizaciones-contactables-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['nombre', 'tipo', 'email', 'telefono', 'web', 'instagram', 'facebook', 'ciudad', 'etapa', 'origen', 'consentimiento', 'finalidad']);

            foreach ($query->cursor() as $organization) {
                fputcsv($out, array_map([$this, 'csvCell'], [
                    $organization->name,
                    $organization->type,
                    $organization->email,
                    $organization->phone,
                    $organization->website,
                    $organization->instagram_url,
                    $organization->facebook_url,
                    $organization->city,
                    $organization->contact_stage,
                    $organization->source,
                    optional($organization->contact_consent_at)->format('Y-m-d'),
                    $organization->contact_consent_purpose,
                ]));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function markContacted(Organization $organization)
    {
        $organization->last_contacted_at = now();

        if (in_array($organization->contact_stage, [null, 'prospect'], true)) {
            $organization->contact_stage = 'contacted';
        }

        $organization->save();

        return back()->with('success', 'Contacto registrado.');
    }

    public function create()
    {
        $organization = new Organization(['type' => 'artista', 'status' => 'active', 'source' => 'manual', 'contact_stage' => 'prospect']);

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

    private function filtered(Request $request): Builder
    {
        return Organization::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('instagram', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('stage'), fn ($query) => $query->where('contact_stage', $request->string('stage')->toString()))
            ->when($request->string('verified')->toString() === '1', fn ($query) => $query->where('is_verified', true))
            ->when($request->string('contactable')->toString() === '1', fn ($query) => $query->contactable());
    }

    /**
     * Neutraliza celdas que una planilla interpretaría como fórmula.
     */
    private function csvCell($value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
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

        $data['contact_stage'] = $data['contact_stage'] ?? ($organization->contact_stage ?: 'prospect');

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
