<?php

namespace App\Services;

use App\Models\EntityClaimRequest;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Solicitud y revisión de vínculos usuario <-> ficha (intérprete, festival, ...).
 */
class EntityClaimService
{
    public function __construct(private OrganizationProvisioner $provisioner)
    {
    }

    /**
     * @param  array{entity_type:string, entity_id:int, relationship:string, message:string, contact_phone?:?string}  $data
     */
    public function request(User $user, array $data): EntityClaimRequest
    {
        $config = config('entity_management.entities.'.$data['entity_type']);
        $entity = $config ? $config['model']::query()->find($data['entity_id']) : null;

        if (! $entity) {
            throw ValidationException::withMessages(['entity_id' => 'La ficha seleccionada no existe.']);
        }

        if ($user->canManage($entity)) {
            throw ValidationException::withMessages(['entity_id' => 'Ya administrás esta ficha.']);
        }

        $alreadyPending = EntityClaimRequest::query()
            ->pending()
            ->where('user_id', $user->getKey())
            ->where('entity_type', $entity->getMorphClass())
            ->where('entity_id', $entity->getKey())
            ->exists();

        if ($alreadyPending) {
            throw ValidationException::withMessages(['entity_id' => 'Ya tenés una solicitud pendiente para esta ficha.']);
        }

        $pendingCount = EntityClaimRequest::query()->pending()->where('user_id', $user->getKey())->count();
        $limit = (int) config('entity_management.claims.max_pending_per_user', 5);

        if ($pendingCount >= $limit) {
            throw ValidationException::withMessages(['entity_id' => "Llegaste al máximo de $limit solicitudes pendientes. Esperá la respuesta o cancelá alguna."]);
        }

        return EntityClaimRequest::create([
            'user_id' => $user->getKey(),
            'entity_type' => $entity->getMorphClass(),
            'entity_id' => $entity->getKey(),
            'relationship' => $data['relationship'],
            'message' => $data['message'],
            'contact_phone' => $data['contact_phone'] ?? null,
            'status' => EntityClaimRequest::PENDING,
        ]);
    }

    /**
     * Solicitud de alta: el usuario no encontró su artista/festival y pide que se cree.
     *
     * @param  array{entity_type:string, proposed_name:string, relationship:string, message:string, evidence_url?:?string, contact_phone?:?string, mes_id?:?int}  $data
     */
    public function requestCreation(User $user, array $data): EntityClaimRequest
    {
        $config = config('entity_management.entities.'.$data['entity_type']);

        if (! $config) {
            throw ValidationException::withMessages(['entity_type' => 'El tipo de ficha no es válido.']);
        }

        $name = trim(preg_replace('/\s+/u', ' ', (string) $data['proposed_name']));
        $morph = (new $config['model'])->getMorphClass();

        $duplicate = EntityClaimRequest::query()
            ->pending()
            ->where('user_id', $user->getKey())
            ->where('kind', EntityClaimRequest::KIND_CREATE)
            ->where('entity_type', $morph)
            ->where('proposed_name', $name)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['proposed_name' => 'Ya tenés una solicitud de alta pendiente con ese nombre.']);
        }

        $this->ensureUnderPendingLimit($user);

        return EntityClaimRequest::create([
            'user_id' => $user->getKey(),
            'kind' => EntityClaimRequest::KIND_CREATE,
            'entity_type' => $morph,
            'entity_id' => null,
            'proposed_name' => $name,
            'relationship' => $data['relationship'],
            'message' => $data['message'],
            'evidence_url' => $data['evidence_url'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'proposed_data' => ($data['entity_type'] === 'festival' && ! empty($data['mes_id'])) ? ['mes_id' => (int) $data['mes_id']] : null,
            'status' => EntityClaimRequest::PENDING,
        ]);
    }

    /**
     * Fichas existentes con nombre parecido, para evitar duplicados (usuario y administrador).
     *
     * @return \Illuminate\Support\Collection<int, array{id:int, name:string}>
     */
    public function similarEntities(string $typeKey, string $name, int $limit = 8)
    {
        $config = config('entity_management.entities.'.$typeKey);
        $name = trim($name);

        if (! $config || mb_strlen($name) < 3) {
            return collect();
        }

        $column = $config['name_column'];
        $tokens = collect(preg_split('/\s+/u', $name))->filter(fn ($token) => mb_strlen($token) >= 4)->take(4);
        $escape = fn (string $value) => addcslashes($value, '%_\\');

        return $config['model']::query()
            ->where(function ($query) use ($column, $name, $tokens, $escape) {
                $query->where($column, 'like', '%'.$escape($name).'%');

                foreach ($tokens as $token) {
                    $query->orWhere($column, 'like', '%'.$escape($token).'%');
                }
            })
            ->orderByRaw("$column = ? desc", [$name])
            ->orderBy($column)
            ->limit($limit)
            ->get(['id', $column])
            ->map(fn ($entity) => ['id' => (int) $entity->getKey(), 'name' => (string) $entity->{$column}]);
    }

    /**
     * Aprueba una solicitud de alta: crea una ficha mínima en estado pendiente de revisión (sin
     * inventar datos) o, si el administrador detecta un duplicado, vincula a una ficha existente.
     * Luego aplica el mismo alta de membresía que una solicitud de vínculo.
     */
    public function approveCreation(EntityClaimRequest $claim, User $reviewer, string $role, ?string $notes = null, ?int $existingEntityId = null): EntityClaimRequest
    {
        $this->ensurePending($claim);

        if (! $claim->isCreation()) {
            throw ValidationException::withMessages(['claim' => 'Esta solicitud no es de alta de ficha.']);
        }

        $typeKey = collect(config('entity_management.entities'))
            ->search(fn ($config) => (new $config['model'])->getMorphClass() === $claim->entity_type);

        if ($typeKey === false) {
            throw ValidationException::withMessages(['claim' => 'El tipo de ficha ya no está registrado.']);
        }

        $config = config("entity_management.entities.$typeKey");

        return DB::transaction(function () use ($claim, $reviewer, $role, $notes, $existingEntityId, $typeKey, $config) {
            if ($existingEntityId) {
                $entity = $config['model']::query()->find($existingEntityId);

                if (! $entity) {
                    throw ValidationException::withMessages(['existing_entity_id' => 'La ficha existente seleccionada no existe.']);
                }
            } else {
                $entity = $this->createStub($typeKey, $claim);
            }

            $claim->update(['entity_id' => $entity->getKey()]);
            $claim->setRelation('entity', $entity);

            return $this->approve($claim, $reviewer, $role, $notes);
        });
    }

    /**
     * Ficha mínima: solo el nombre propuesto. No inventa biografía ni datos; queda sin publicar
     * (estado 0 / draft) y a cargo del usuario, que la completa y la envía a revisión.
     */
    private function createStub(string $typeKey, EntityClaimRequest $claim): Model
    {
        $name = (string) $claim->proposed_name;

        if ($typeKey === 'interprete') {
            $entity = new Interprete(['interprete' => $name, 'biografia' => '']);
            $entity->slug = $this->uniqueSlug(Interprete::class, 'slug', $name);
            $entity->estado = 0;
            $entity->user_id = $claim->user_id;
            $entity->save();

            return $entity;
        }

        if ($typeKey === 'festival') {
            $mesId = $claim->proposed_data['mes_id'] ?? null;

            if (! $mesId || ! Mes::query()->whereKey($mesId)->exists()) {
                throw ValidationException::withMessages(['claim' => 'El festival necesita un mes válido para crearse. Vinculá una ficha existente o rechazá la solicitud.']);
            }

            $entity = new Festival(['title' => $name, 'status' => 'draft']);
            $entity->slug = $this->uniqueSlug(Festival::class, 'slug', $name);
            $entity->mes_id = $mesId;
            $entity->user_id = $claim->user_id;
            $entity->save();

            return $entity;
        }

        throw ValidationException::withMessages(['claim' => 'No se puede crear automáticamente este tipo de ficha.']);
    }

    private function uniqueSlug(string $model, string $column, string $name): string
    {
        $base = Str::slug($name) ?: 'ficha';
        $slug = $base;
        $suffix = 2;

        while ($model::query()->where($column, $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function ensureUnderPendingLimit(User $user): void
    {
        $limit = (int) config('entity_management.claims.max_pending_per_user', 5);

        if (EntityClaimRequest::query()->pending()->where('user_id', $user->getKey())->count() >= $limit) {
            throw ValidationException::withMessages(['entity_id' => "Llegaste al máximo de $limit solicitudes pendientes. Esperá la respuesta o cancelá alguna."]);
        }
    }

    /**
     * Rol de membresía sugerido al aprobar, con el motivo y las alertas que el administrador debe ver.
     * Solo orienta: no aprueba ni cambia nada.
     *
     * @return array{role:string, reason:string, alerts:array<int, array{level:string, text:string}>}
     */
    public function suggestApproval(EntityClaimRequest $claim, int $similarCount = 0): array
    {
        $relationships = config('entity_management.claims.relationships');
        $declared = $relationships[$claim->relationship] ?? $claim->relationship;
        $role = config('entity_management.claims.suggested_roles.'.$claim->relationship, 'editor');
        $alerts = [];

        $organizations = $claim->entity
            ? $claim->entity->organizations()->orderByDesc('organization_entities.is_primary')->orderBy('organizations.id')->get()
            : collect();
        $active = $organizations->firstWhere('status', 'active');

        $activeOwner = $active
            ? OrganizationMember::query()
                ->with('user:id,name')
                ->where('organization_id', $active->getKey())
                ->where('status', 'active')
                ->where('role', 'owner')
                ->where('user_id', '!=', $claim->user_id)
                ->first()
            : null;

        $reason = "Declaró: {$declared}.";

        if ($role === 'owner' && $activeOwner) {
            $role = 'editor';
            $reason .= ' La ficha ya tiene un responsable activo, por eso se sugiere Editor.';
        } elseif ($role === 'owner') {
            $reason .= ' La ficha no tiene un responsable activo: se sugiere Responsable (owner).';
        } else {
            $reason .= ' Se sugiere Editor.';
        }

        if ($organizations->isNotEmpty() && ! $active) {
            $alerts[] = ['level' => 'danger', 'text' => 'La organización de esta ficha está inactiva: la aprobación se va a rechazar hasta que la actives o vincules otra.'];
        }

        if ($activeOwner) {
            $alerts[] = ['level' => 'info', 'text' => 'La ficha ya tiene un responsable activo: '.($activeOwner->user?->name ?? 'usuario #'.$activeOwner->user_id).'.'];
        }

        if (in_array($claim->relationship, ['press', 'other'], true) && ! $activeOwner) {
            $alerts[] = ['level' => 'warning', 'text' => "Declaró \"{$declared}\" y la ficha no tiene responsable activo: confirmá quién debería controlarla antes de aprobar."];
        }

        if ($active) {
            $existing = OrganizationMember::query()->where('organization_id', $active->getKey())->where('user_id', $claim->user_id)->first();

            if ($existing) {
                $alerts[] = ['level' => 'warning', 'text' => "El usuario ya es miembro de la organización (rol {$existing->role}, estado {$existing->status}): aprobar actualiza su rol y lo deja activo."];
            }
        }

        if ($claim->isCreation()) {
            if (blank($claim->evidence_url)) {
                $alerts[] = ['level' => 'warning', 'text' => 'No incluyó link de respaldo.'];
            }

            if ($similarCount > 0) {
                $alerts[] = ['level' => 'warning', 'text' => "Hay {$similarCount} ficha(s) con nombre parecido: revisá si es un duplicado antes de crear una nueva."];
            }
        }

        return ['role' => $role, 'reason' => $reason, 'alerts' => $alerts];
    }

    /** Roles de membresía que un owner puede otorgar al revisar (nunca 'owner'). */
    public const DELEGATED_ROLES = ['editor', 'member'];

    public function delegatedReviewEnabled(): bool
    {
        return (bool) config('entity_management.claims.delegate_to_owners', true);
    }

    /**
     * Organización activa de la ficha pedida donde el usuario es owner activo; null si no puede
     * revisar la solicitud por delegación (propia, de alta, ya resuelta, sin owner activo, etc.).
     */
    public function delegatedOrganization(User $user, EntityClaimRequest $claim): ?Organization
    {
        if (! $this->delegatedReviewEnabled()
            || $claim->kind !== EntityClaimRequest::KIND_LINK
            || ! $claim->isPending()
            || ! $claim->entity_id
            || (int) $claim->user_id === (int) $user->getKey()) {
            return null;
        }

        $organizationId = DB::table('organization_entities as oe')
            ->join('organization_members as om', 'om.organization_id', '=', 'oe.organization_id')
            ->join('organizations as o', 'o.id', '=', 'oe.organization_id')
            ->where('oe.entity_type', $claim->entity_type)
            ->where('oe.entity_id', $claim->entity_id)
            ->where('om.user_id', $user->getKey())
            ->where('om.role', 'owner')
            ->where('om.status', 'active')
            ->where('o.status', 'active')
            ->orderByDesc('oe.is_primary')
            ->value('o.id');

        return $organizationId ? Organization::query()->find($organizationId) : null;
    }

    /**
     * Solicitudes pendientes de vínculo sobre fichas donde el usuario es owner activo.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, EntityClaimRequest>
     */
    public function claimsToReview(User $user, int $limit = 30)
    {
        if (! $this->delegatedReviewEnabled()) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return EntityClaimRequest::query()
            ->with(['user:id,name', 'entity'])
            ->pending()
            ->where('kind', EntityClaimRequest::KIND_LINK)
            ->where('user_id', '!=', $user->getKey())
            ->whereExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('organization_entities as oe')
                    ->join('organization_members as om', 'om.organization_id', '=', 'oe.organization_id')
                    ->join('organizations as o', 'o.id', '=', 'oe.organization_id')
                    ->whereColumn('oe.entity_type', 'entity_claim_requests.entity_type')
                    ->whereColumn('oe.entity_id', 'entity_claim_requests.entity_id')
                    ->where('om.user_id', $user->getKey())
                    ->where('om.role', 'owner')
                    ->where('om.status', 'active')
                    ->where('o.status', 'active');
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    public function approveAsOwner(EntityClaimRequest $claim, User $owner, string $role, ?string $notes = null): EntityClaimRequest
    {
        $organization = $this->delegatedOrganization($owner, $claim);

        if (! $organization) {
            throw ValidationException::withMessages(['claim' => 'No podés revisar esta solicitud.']);
        }

        if (! in_array($role, self::DELEGATED_ROLES, true)) {
            throw ValidationException::withMessages(['role' => 'Solo podés otorgar el rol Editor o Miembro.']);
        }

        $existing = OrganizationMember::query()->where('organization_id', $organization->getKey())->where('user_id', $claim->user_id)->first();

        if ($existing && $existing->role === 'owner' && $existing->status === 'active') {
            throw ValidationException::withMessages(['claim' => 'Esta persona ya es responsable de la ficha.']);
        }

        return DB::transaction(function () use ($claim, $owner, $organization, $role, $notes) {
            OrganizationMember::query()->updateOrCreate(
                ['organization_id' => $organization->getKey(), 'user_id' => $claim->user_id],
                ['role' => $role, 'status' => 'active']
            );

            $claim->update([
                'status' => EntityClaimRequest::APPROVED,
                'organization_id' => $organization->getKey(),
                'reviewed_by' => $owner->getKey(),
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            return $claim;
        });
    }

    public function rejectAsOwner(EntityClaimRequest $claim, User $owner, string $notes): EntityClaimRequest
    {
        if (! $this->delegatedOrganization($owner, $claim)) {
            throw ValidationException::withMessages(['claim' => 'No podés revisar esta solicitud.']);
        }

        return $this->reject($claim, $owner, $notes);
    }

    public function cancel(EntityClaimRequest $claim): EntityClaimRequest
    {
        $this->ensurePending($claim);
        $claim->update(['status' => EntityClaimRequest::CANCELLED]);

        return $claim;
    }

    public function approve(EntityClaimRequest $claim, User $reviewer, string $role, ?string $notes = null): EntityClaimRequest
    {
        $this->ensurePending($claim);

        $entity = $claim->entity;

        if (! $entity) {
            throw ValidationException::withMessages(['claim' => 'La ficha ya no existe.']);
        }

        return DB::transaction(function () use ($claim, $entity, $reviewer, $role, $notes) {
            $organization = $this->organizationFor($entity);

            OrganizationMember::query()->updateOrCreate(
                ['organization_id' => $organization->getKey(), 'user_id' => $claim->user_id],
                ['role' => $role, 'status' => 'active']
            );

            $claim->update([
                'status' => EntityClaimRequest::APPROVED,
                'organization_id' => $organization->getKey(),
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            return $claim;
        });
    }

    public function reject(EntityClaimRequest $claim, User $reviewer, ?string $notes = null): EntityClaimRequest
    {
        $this->ensurePending($claim);

        $claim->update([
            'status' => EntityClaimRequest::REJECTED,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        return $claim;
    }

    /**
     * Organización activa de la ficha; si no tiene ninguna se crea la principal.
     * Si la ficha solo tiene organizaciones inactivas la aprobación se rechaza,
     * porque un vínculo con una organización inactiva no otorgaría permisos.
     */
    private function organizationFor(Model $entity)
    {
        $organizations = $entity->organizations()->orderByDesc('organization_entities.is_primary')->orderBy('organizations.id')->get();

        if ($organizations->isEmpty()) {
            return $this->provisioner->forEntity($entity);
        }

        $active = $organizations->firstWhere('status', 'active');

        if (! $active) {
            throw ValidationException::withMessages([
                'claim' => 'La organización de esta ficha está inactiva. Activala (o vinculá otra) antes de aprobar.',
            ]);
        }

        return $active;
    }

    private function ensurePending(EntityClaimRequest $claim): void
    {
        if (! $claim->isPending()) {
            throw ValidationException::withMessages(['claim' => 'Esta solicitud ya fue resuelta.']);
        }
    }
}
