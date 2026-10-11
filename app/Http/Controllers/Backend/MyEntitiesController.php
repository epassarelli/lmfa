<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EntityClaimRequest;
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

        return view('backend.my-entities.index', compact('managed', 'claims', 'prefill', 'showNoFichasBanner'));
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
