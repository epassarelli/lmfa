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

        return view('backend.claims.show', compact('claim', 'organizations', 'defaultRole'));
    }

    public function approve(Request $request, EntityClaimRequest $claim)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(config('entity_management.member_roles')))],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ], [], ['role' => 'rol', 'review_notes' => 'nota']);

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
