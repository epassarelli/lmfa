<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EntityClaimRequest;
use App\Models\Mes;
use App\Services\EntityClaimService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * "Mis fichas": cualquier usuario autenticado (login común, Google o Facebook)
 * ve lo que administra y puede solicitar administrar un intérprete o festival.
 */
class MyEntitiesController extends Controller
{
    private const MANAGED_LIMIT = 50;

    public function __construct(private EntityClaimService $claims)
    {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $types = config('entity_management.entities');

        $managed = [];
        foreach ($types as $key => $config) {
            $column = $config['name_column'];

            foreach ($config['model']::query()->managedOrLegacyBy($user)->orderBy($column)->limit(self::MANAGED_LIMIT)->get(['id', $column]) as $entity) {
                $managed[] = [
                    'label' => $config['label'],
                    'name' => $entity->{$column},
                    'edit_url' => route($config['edit_route'], $entity->id),
                ];
            }
        }

        $claims = EntityClaimRequest::query()
            ->with(['entity', 'organization:id,name'])
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(30)
            ->get();

        $prefill = null;
        $prefillType = $request->string('type')->toString();
        if (isset($types[$prefillType]) && $request->integer('id') > 0) {
            $entity = $types[$prefillType]['model']::query()->find($request->integer('id'));
            if ($entity) {
                $prefill = ['type' => $prefillType, 'id' => $entity->id, 'text' => $entity->{$types[$prefillType]['name_column']}.' (#'.$entity->id.')'];
            }
        }

        $showNoFichasBanner = config('backoffice_access.enabled') && $user->hasNoManagedEntities() && ! $user->hasRole('administrador');

        $meses = Mes::query()->orderBy('id')->get(['id', 'nombre']);
        $toReview = $this->claims->claimsToReview($user);

        return view('backend.my-entities.index', compact('managed', 'claims', 'prefill', 'showNoFichasBanner', 'meses', 'toReview'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entity_type' => ['required', Rule::in(array_keys(config('entity_management.entities')))],
            'entity_id' => ['required', 'integer', 'min:1'],
            'relationship' => ['required', Rule::in(array_keys(config('entity_management.claims.relationships')))],
            'message' => ['required', 'string', 'min:10', 'max:1000'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ], [], [
            'entity_type' => 'tipo de ficha',
            'entity_id' => 'ficha',
            'relationship' => 'relación con la ficha',
            'message' => 'mensaje',
            'contact_phone' => 'teléfono',
        ]);

        $this->claims->request(Auth::user(), $data);

        return redirect()->route('backend.my-entities.index')
            ->with('success', 'Recibimos tu solicitud. La revisamos y te avisamos por acá el resultado.');
    }

    public function storeCreation(Request $request)
    {
        $data = $request->validate([
            'entity_type' => ['required', Rule::in(array_keys(config('entity_management.entities')))],
            'proposed_name' => ['required', 'string', 'min:3', 'max:255'],
            'mes_id' => ['nullable', 'required_if:entity_type,festival', 'integer', Rule::exists((new Mes)->getTable(), 'id')],
            'relationship' => ['required', Rule::in(array_keys(config('entity_management.claims.relationships')))],
            'message' => ['required', 'string', 'min:10', 'max:1000'],
            'evidence_url' => ['nullable', 'url', 'max:2048'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ], [], [
            'entity_type' => 'tipo de ficha',
            'proposed_name' => 'nombre',
            'mes_id' => 'mes del festival',
            'relationship' => 'relación con la ficha',
            'message' => 'mensaje',
            'evidence_url' => 'link de respaldo',
            'contact_phone' => 'teléfono',
        ]);

        $this->claims->requestCreation(Auth::user(), $data);

        return redirect()->route('backend.my-entities.index')
            ->with('success', 'Recibimos tu solicitud de alta. Un administrador la revisa y te avisamos por acá el resultado.');
    }

    public function similar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(config('entity_management.entities')))],
            'q' => ['required', 'string', 'max:255'],
        ]);

        return response()->json(['results' => $this->claims->similarEntities($data['type'], $data['q'])->values()]);
    }

    public function reviewApprove(Request $request, EntityClaimRequest $claim)
    {
        abort_unless($this->claims->delegatedOrganization(Auth::user(), $claim), 403);

        $data = $request->validate([
            'role' => ['required', Rule::in(EntityClaimService::DELEGATED_ROLES)],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ], [], ['role' => 'rol', 'review_notes' => 'nota']);

        $this->claims->approveAsOwner($claim, Auth::user(), $data['role'], $data['review_notes'] ?? null);

        return redirect()->route('backend.my-entities.index')->with('success', 'Solicitud aprobada: la persona ya puede editar la ficha.');
    }

    public function reviewReject(Request $request, EntityClaimRequest $claim)
    {
        abort_unless($this->claims->delegatedOrganization(Auth::user(), $claim), 403);

        $data = $request->validate([
            'review_notes' => ['required', 'string', 'min:3', 'max:1000'],
        ], [], ['review_notes' => 'motivo']);

        $this->claims->rejectAsOwner($claim, Auth::user(), $data['review_notes']);

        return redirect()->route('backend.my-entities.index')->with('success', 'Solicitud rechazada.');
    }

    public function cancel(EntityClaimRequest $claim)
    {
        abort_unless((int) $claim->user_id === (int) Auth::id(), 403);

        $this->claims->cancel($claim);

        return back()->with('success', 'Solicitud cancelada.');
    }

    public function search(Request $request): JsonResponse
    {
        return app(OrganizationLookupController::class)->entities($request);
    }
}
