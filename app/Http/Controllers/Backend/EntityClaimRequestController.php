<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EntityClaimRequest;
use App\Services\EntityClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EntityClaimRequestController extends Controller
{
    public function __construct(private EntityClaimService $claims)
    {
    }

    public function index(Request $request)
    {
        $status = $request->string('status', EntityClaimRequest::PENDING)->toString();

        $requests = EntityClaimRequest::query()
            ->with(['user:id,name,email', 'entity'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderByRaw("status = 'pending' desc")
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $pendingCount = EntityClaimRequest::query()->pending()->count();

        return view('backend.claims.index', compact('requests', 'status', 'pendingCount'));
    }

    public function show(EntityClaimRequest $claim)
    {
        $claim->load(['user:id,name,email,phone', 'entity', 'reviewer:id,name', 'organization:id,name']);

        $organizations = $claim->entity
            ? $claim->entity->organizations()->with(['members' => fn ($query) => $query->where('status', 'active')->where('role', 'owner')])->get()
            : collect();

        $activeOrganization = $organizations->firstWhere('status', 'active');
        $defaultRole = $activeOrganization && $activeOrganization->members->isNotEmpty() ? 'editor' : 'owner';

        $similar = collect();
        $typeKey = null;

        if ($claim->isCreation() && ! $claim->entity_id) {
            $typeKey = collect(config('entity_management.entities'))->search(fn ($config) => (new $config['model'])->getMorphClass() === $claim->entity_type);
            $similar = $typeKey !== false ? $this->claims->similarEntities($typeKey, (string) $claim->proposed_name) : collect();
            $defaultRole = 'owner';
        }

        return view('backend.claims.show', compact('claim', 'organizations', 'defaultRole', 'similar'));
    }

    public function approve(Request $request, EntityClaimRequest $claim)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(config('entity_management.member_roles')))],
            'review_notes' => ['nullable', 'string', 'max:1000'],
            'existing_entity_id' => ['nullable', 'integer', 'min:1'],
        ], [], ['role' => 'rol', 'review_notes' => 'nota', 'existing_entity_id' => 'ficha existente']);

        if ($claim->isCreation() && ! $claim->entity_id) {
            $this->claims->approveCreation($claim, Auth::user(), $data['role'], $data['review_notes'] ?? null, $data['existing_entity_id'] ?? null);

            return redirect()->route('backend.claims.show', $claim)->with('success', empty($data['existing_entity_id'])
                ? 'Solicitud aprobada: se creó la ficha (sin publicar) y el usuario ya puede completarla.'
                : 'Solicitud aprobada: el usuario quedó vinculado a la ficha existente.');
        }

        $this->claims->approve($claim, Auth::user(), $data['role'], $data['review_notes'] ?? null);

        return redirect()->route('backend.claims.show', $claim)->with('success', 'Solicitud aprobada: el usuario ya puede administrar la ficha.');
    }

    public function reject(Request $request, EntityClaimRequest $claim)
    {
        $data = $request->validate([
            'review_notes' => ['required', 'string', 'min:3', 'max:1000'],
        ], [], ['review_notes' => 'motivo']);

        $this->claims->reject($claim, Auth::user(), $data['review_notes']);

        return redirect()->route('backend.claims.show', $claim)->with('success', 'Solicitud rechazada.');
    }
}
