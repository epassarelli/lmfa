<?php

namespace Tests\Feature\EntityManagement;

use App\Models\EntityClaimRequest;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EntityClaimRequestTest extends TestCase
{
    use DatabaseTransactions;

    private function plainUser(): User
    {
        return User::factory()->create(); // sin roles: como quien se registra por Google, Facebook o el login común
    }

    private function admin(): User
    {
        Role::findOrCreate('administrador', 'web');
        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    private function interprete(string $slug, int $userId = 1): Interprete
    {
        return Interprete::create([
            'interprete' => 'Artista '.$slug,
            'slug' => $slug,
            'biografia' => 'Biografía de prueba.',
            'estado' => 1,
            'user_id' => $userId,
        ]);
    }

    private function festival(string $slug): Festival
    {
        $mes = Mes::first() ?: Mes::create(['nombre' => 'Enero']);

        return Festival::forceCreate(['title' => 'Festival '.$slug, 'slug' => $slug, 'mes_id' => $mes->id, 'user_id' => 1]);
    }

    private function linkedOrganization($entity, string $status = 'active', ?User $owner = null): Organization
    {
        $organization = Organization::create(['type' => 'artista', 'name' => 'Org '.uniqid(), 'status' => $status]);
        OrganizationEntity::create(['organization_id' => $organization->id, 'entity_type' => $entity->getMorphClass(), 'entity_id' => $entity->getKey(), 'is_primary' => true]);

        if ($owner) {
            OrganizationMember::create(['organization_id' => $organization->id, 'user_id' => $owner->id, 'role' => 'owner', 'status' => 'active']);
        }

        return $organization;
    }

    private function claimPayload($entity, array $overrides = []): array
    {
        return array_merge([
            'entity_type' => $entity instanceof Festival ? 'festival' : 'interprete',
            'entity_id' => $entity->id,
            'relationship' => 'artist',
            'message' => 'Soy parte del grupo y puedo demostrarlo.',
        ], $overrides);
    }

    private function claim(User $user, $entity, string $status = 'pending'): EntityClaimRequest
    {
        return EntityClaimRequest::create([
            'user_id' => $user->id,
            'entity_type' => $entity->getMorphClass(),
            'entity_id' => $entity->getKey(),
            'relationship' => 'artist',
            'message' => 'Mensaje de prueba de la solicitud.',
            'status' => $status,
        ]);
    }

    /** @test */
    public function guests_are_redirected_and_users_without_roles_can_open_my_entities(): void
    {
        $this->get(route('backend.my-entities.index'))->assertRedirect();

        $this->actingAs($this->plainUser());
        $this->get(route('backend.my-entities.index'))
            ->assertOk()
            ->assertSee('Solicitar administrar una ficha');
    }

    /** @test */
    public function any_authenticated_user_can_submit_a_claim(): void
    {
        $user = $this->plainUser();
        $artist = $this->interprete('cl-submit');
        $this->actingAs($user);

        $this->post(route('backend.my-entities.claims.store'), $this->claimPayload($artist, ['contact_phone' => '+54 9 351 000 0000']))
            ->assertRedirect(route('backend.my-entities.index'))
            ->assertSessionHasNoErrors();

        $claim = EntityClaimRequest::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('pending', $claim->status);
        $this->assertSame(Interprete::class, $claim->entity_type);
        $this->assertSame($artist->id, (int) $claim->entity_id);
        $this->assertSame('+54 9 351 000 0000', $claim->contact_phone);

        $this->get(route('backend.my-entities.index'))->assertSee($artist->interprete)->assertSee('Pendiente');
    }

    /** @test */
    public function claims_are_validated(): void
    {
        $this->actingAs($this->plainUser());
        $artist = $this->interprete('cl-validate');

        $this->post(route('backend.my-entities.claims.store'), $this->claimPayload($artist, ['message' => 'corto']))->assertSessionHasErrors('message');
        $this->post(route('backend.my-entities.claims.store'), $this->claimPayload($artist, ['relationship' => 'inventada']))->assertSessionHasErrors('relationship');
        $this->post(route('backend.my-entities.claims.store'), $this->claimPayload($artist, ['entity_type' => 'noticia']))->assertSessionHasErrors('entity_type');
        $this->post(route('backend.my-entities.claims.store'), $this->claimPayload($artist, ['entity_id' => 999999999]))->assertSessionHasErrors('entity_id');
        $this->assertSame(0, EntityClaimRequest::where('user_id', auth()->id())->count());
    }

    /** @test */
    public function duplicate_pending_claims_and_the_pending_limit_are_rejected(): void
    {
        $user = $this->plainUser();
        $this->actingAs($user);
        $artist = $this->interprete('cl-dup');

        $this->post(route('backend.my-entities.claims.store'), $this->claimPayload($artist))->assertSessionHasNoErrors();
        $this->post(route('backend.my-entities.claims.store'), $this->claimPayload($artist))->assertSessionHasErrors('entity_id');
        $this->assertSame(1, EntityClaimRequest::where('user_id', $user->id)->count());

        $max = (int) config('entity_management.claims.max_pending_per_user');
        for ($i = 1; $i < $max; $i++) {
            $this->post(route('backend.my-entities.claims.store'), $this->claimPayload($this->interprete("cl-lim-$i")))->assertSessionHasNoErrors();
        }
        $this->assertSame($max, EntityClaimRequest::where('user_id', $user->id)->pending()->count());

        $this->post(route('backend.my-entities.claims.store'), $this->claimPayload($this->interprete('cl-lim-extra')))->assertSessionHasErrors('entity_id');
        $this->assertSame($max, EntityClaimRequest::where('user_id', $user->id)->count());
    }

    /** @test */
    public function users_cannot_claim_an_entity_they_already_manage(): void
    {
        $user = $this->plainUser();
        $this->actingAs($user);
        $mine = $this->interprete('cl-mine', $user->id);

        $this->post(route('backend.my-entities.claims.store'), $this->claimPayload($mine))->assertSessionHasErrors('entity_id');

        $other = $this->interprete('cl-member');
        $organization = $this->linkedOrganization($other);
        OrganizationMember::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'role' => 'editor', 'status' => 'active']);
        $user->forgetManagedEntities();

        $this->post(route('backend.my-entities.claims.store'), $this->claimPayload($other))->assertSessionHasErrors('entity_id');
        $this->assertSame(0, EntityClaimRequest::where('user_id', $user->id)->count());
    }

    /** @test */
    public function users_can_cancel_only_their_own_pending_claims(): void
    {
        $user = $this->plainUser();
        $other = $this->plainUser();
        $artist = $this->interprete('cl-cancel');
        $mine = $this->claim($user, $artist);
        $theirs = $this->claim($other, $artist);

        $this->actingAs($user);
        $this->post(route('backend.my-entities.claims.cancel', $theirs))->assertForbidden();
        $this->assertSame('pending', $theirs->fresh()->status);

        $this->post(route('backend.my-entities.claims.cancel', $mine))->assertRedirect();
        $this->assertSame('cancelled', $mine->fresh()->status);

        $this->post(route('backend.my-entities.claims.cancel', $mine))->assertSessionHasErrors('claim');
    }

    /** @test */
    public function my_entities_lists_managed_entities_and_prefills_from_the_query(): void
    {
        $user = $this->plainUser();
        $managed = $this->interprete('cl-managed');
        $target = $this->interprete('cl-prefill');
        $organization = $this->linkedOrganization($managed);
        OrganizationMember::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'role' => 'editor', 'status' => 'active']);

        $this->actingAs($user);
        $this->get(route('backend.my-entities.index', ['type' => 'interprete', 'id' => $target->id]))
            ->assertOk()
            ->assertSee($managed->interprete)
            ->assertSee(route('backend.interpretes.edit', $managed->id), false)
            ->assertSee($target->interprete.' (#'.$target->id.')', false);
    }

    /** @test */
    public function entity_search_is_available_to_authenticated_users_with_a_minimum_length(): void
    {
        $artist = $this->interprete('cl-search');
        $this->getJson(route('backend.my-entities.search', ['type' => 'interprete', 'q' => 'Artista cl-search']))->assertUnauthorized();

        $this->actingAs($this->plainUser());
        $this->getJson(route('backend.my-entities.search', ['type' => 'interprete', 'q' => 'Artista cl-search']))
            ->assertOk()->assertJsonFragment(['id' => $artist->id]);
        $this->getJson(route('backend.my-entities.search', ['type' => 'interprete', 'q' => 'a']))
            ->assertOk()->assertExactJson(['results' => []]);
    }

    /** @test */
    public function approving_a_claim_on_an_entity_without_organization_creates_it_and_grants_access(): void
    {
        $admin = $this->admin();
        $user = $this->plainUser();
        $artist = $this->interprete('cl-approve-new');
        $claim = $this->claim($user, $artist);

        $this->assertFalse($user->canManage($artist));

        $this->actingAs($admin)
            ->post(route('backend.claims.approve', $claim), ['role' => 'owner', 'review_notes' => 'Verificado por Instagram.'])
            ->assertRedirect(route('backend.claims.show', $claim));

        $claim->refresh();
        $this->assertSame('approved', $claim->status);
        $this->assertSame($admin->id, (int) $claim->reviewed_by);
        $this->assertNotNull($claim->reviewed_at);
        $this->assertNotNull($claim->organization_id);
        $this->assertSame('Verificado por Instagram.', $claim->review_notes);

        $member = OrganizationMember::where('organization_id', $claim->organization_id)->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('owner', $member->role);
        $this->assertSame('active', $member->status);
        $this->assertTrue(User::find($user->id)->canManage($artist));
    }

    /** @test */
    public function approving_reuses_the_active_organization_of_the_entity(): void
    {
        $user = $this->plainUser();
        $artist = $this->interprete('cl-approve-reuse');
        $organization = $this->linkedOrganization($artist, 'active', $this->plainUser());
        $claim = $this->claim($user, $artist);
        $organizationsBefore = Organization::count();

        $this->actingAs($this->admin())->post(route('backend.claims.approve', $claim), ['role' => 'editor'])->assertRedirect();

        $this->assertSame($organizationsBefore, Organization::count());
        $this->assertSame($organization->id, (int) $claim->fresh()->organization_id);
        $this->assertTrue(User::find($user->id)->canManage($artist));
    }

    /** @test */
    public function rejecting_requires_a_note_and_grants_nothing(): void
    {
        $user = $this->plainUser();
        $artist = $this->interprete('cl-reject');
        $claim = $this->claim($user, $artist);
        $this->actingAs($this->admin());

        $this->post(route('backend.claims.reject', $claim), ['review_notes' => ''])->assertSessionHasErrors('review_notes');
        $this->assertSame('pending', $claim->fresh()->status);

        $this->post(route('backend.claims.reject', $claim), ['review_notes' => 'No pudimos verificar tu relación.'])->assertRedirect();
        $this->assertSame('rejected', $claim->fresh()->status);
        $this->assertSame(0, OrganizationMember::where('user_id', $user->id)->count());
        $this->assertFalse(User::find($user->id)->canManage($artist));

        $this->actingAs($user)->get(route('backend.my-entities.index'))->assertSee('No pudimos verificar tu relación.');
    }

    /** @test */
    public function resolved_claims_cannot_be_reviewed_again(): void
    {
        $artist = $this->interprete('cl-twice');
        $this->actingAs($this->admin());

        $approved = $this->claim($this->plainUser(), $artist);
        $this->post(route('backend.claims.approve', $approved), ['role' => 'editor'])->assertRedirect();
        $this->post(route('backend.claims.approve', $approved), ['role' => 'owner'])->assertSessionHasErrors('claim');
        $this->post(route('backend.claims.reject', $approved), ['review_notes' => 'Motivo'])->assertSessionHasErrors('claim');
        $this->assertSame('approved', $approved->fresh()->status);

        $rejected = $this->claim($this->plainUser(), $artist);
        $this->post(route('backend.claims.reject', $rejected), ['review_notes' => 'Motivo válido'])->assertRedirect();
        $this->post(route('backend.claims.approve', $rejected), ['role' => 'editor'])->assertSessionHasErrors('claim');
        $this->assertSame('rejected', $rejected->fresh()->status);
    }

    /** @test */
    public function approval_is_refused_when_the_only_organization_is_inactive(): void
    {
        $user = $this->plainUser();
        $artist = $this->interprete('cl-inactive-org');
        $this->linkedOrganization($artist, 'inactive');
        $claim = $this->claim($user, $artist);

        $this->actingAs($this->admin())->post(route('backend.claims.approve', $claim), ['role' => 'editor'])->assertSessionHasErrors('claim');

        $this->assertSame('pending', $claim->fresh()->status);
        $this->assertSame(0, OrganizationMember::where('user_id', $user->id)->count());
    }

    /** @test */
    public function festivals_can_be_claimed_and_approved_too(): void
    {
        $user = $this->plainUser();
        $festival = $this->festival('cl-festival-'.uniqid());
        $this->actingAs($user)->post(route('backend.my-entities.claims.store'), $this->claimPayload($festival, ['relationship' => 'organizer']))->assertSessionHasNoErrors();
        $claim = EntityClaimRequest::where('user_id', $user->id)->firstOrFail();

        $this->actingAs($this->admin())->post(route('backend.claims.approve', $claim), ['role' => 'owner'])->assertRedirect();

        $this->assertTrue(User::find($user->id)->canManage($festival));
    }

    /** @test */
    public function only_administrators_can_review_claims(): void
    {
        $artist = $this->interprete('cl-perm');
        $claim = $this->claim($this->plainUser(), $artist);

        $this->actingAs($this->plainUser());
        $this->get(route('backend.claims.index'))->assertForbidden();
        $this->get(route('backend.claims.show', $claim))->assertForbidden();
        $this->post(route('backend.claims.approve', $claim), ['role' => 'editor'])->assertForbidden();
        $this->post(route('backend.claims.reject', $claim), ['review_notes' => 'Motivo'])->assertForbidden();
        $this->assertSame('pending', $claim->fresh()->status);
    }

    /** @test */
    public function admin_listing_filters_by_status_and_detail_page_renders(): void
    {
        $artist = $this->interprete('cl-list');
        $pendingUser = User::factory()->create(['name' => 'Pendiente Persona']);
        $cancelledUser = User::factory()->create(['name' => 'Cancelada Persona']);
        $pending = $this->claim($pendingUser, $artist);
        $this->claim($cancelledUser, $artist, 'cancelled');

        $this->actingAs($this->admin());
        $this->get(route('backend.claims.index'))->assertOk()->assertSee('Pendiente Persona')->assertDontSee('Cancelada Persona');
        $this->get(route('backend.claims.index', ['status' => 'cancelled']))->assertOk()->assertSee('Cancelada Persona')->assertDontSee('Pendiente Persona');
        $this->get(route('backend.claims.index', ['status' => 'all']))->assertOk()->assertSee('Pendiente Persona')->assertSee('Cancelada Persona');

        $this->get(route('backend.claims.show', $pending))
            ->assertOk()
            ->assertSee('Pendiente Persona')
            ->assertSee($artist->interprete)
            ->assertSee('Mensaje de prueba de la solicitud.');
    }
}
