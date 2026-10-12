<?php

namespace Tests\Feature\EntityManagement;

use App\Models\EntityClaimRequest;
use App\Models\Interprete;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DelegatedClaimReviewTest extends TestCase
{
    use DatabaseTransactions;

    private function user(?string $role = null, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        if ($role) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }

        return $user;
    }

    private function artist(): Interprete
    {
        return Interprete::create(['interprete' => 'Artista '.uniqid(), 'slug' => 'a-'.uniqid(), 'biografia' => 'Bio de prueba.', 'estado' => 1, 'user_id' => $this->baseUserId()]);
    }

    private function organization(Interprete $artist, string $status = 'active'): Organization
    {
        $org = Organization::create(['type' => 'artista', 'name' => 'Org '.uniqid(), 'status' => $status]);
        OrganizationEntity::create(['organization_id' => $org->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id, 'is_primary' => true]);

        return $org;
    }

    private function member(Organization $org, User $user, string $role, string $status = 'active'): void
    {
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => $role, 'status' => $status]);
    }

    private function claim(User $requester, Interprete $artist, array $extra = []): EntityClaimRequest
    {
        return EntityClaimRequest::create(array_merge([
            'user_id' => $requester->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id,
            'relationship' => 'press', 'message' => 'Soy el prensa del artista y puedo acreditarlo.', 'status' => 'pending',
        ], $extra));
    }

    public function test_owner_sees_pending_requests_for_their_ficha_with_limited_data(): void
    {
        $owner = $this->user();
        $requester = $this->user(null, ['name' => 'Persona Solicitante', 'email' => 'secreto@example.test']);
        $artist = $this->artist();
        $this->member($this->organization($artist), $owner, 'owner');
        $this->claim($requester, $artist);

        $this->actingAs($owner)->get(route('backend.my-entities.index'))->assertOk()
            ->assertSee('Solicitudes para mis fichas')
            ->assertSee('Persona Solicitante')
            ->assertSee('Soy el prensa del artista')
            ->assertDontSee('secreto@example.test');
    }

    public function test_owner_approves_as_editor_and_it_is_audited(): void
    {
        $owner = $this->user();
        $requester = $this->user();
        $artist = $this->artist();
        $org = $this->organization($artist);
        $this->member($org, $owner, 'owner');
        $claim = $this->claim($requester, $artist);

        $this->actingAs($owner)->post(route('backend.my-entities.review.approve', $claim), ['role' => 'editor', 'review_notes' => 'Lo conozco.'])->assertSessionHasNoErrors()->assertRedirect(route('backend.my-entities.index'));

        $claim->refresh();
        $this->assertSame('approved', $claim->status);
        $this->assertSame($owner->id, (int) $claim->reviewed_by);
        $this->assertSame($org->id, (int) $claim->organization_id);
        $this->assertTrue(OrganizationMember::where(['organization_id' => $org->id, 'user_id' => $requester->id, 'role' => 'editor', 'status' => 'active'])->exists());
        $this->assertTrue(User::find($requester->id)->canManage($artist));
    }

    public function test_owner_cannot_grant_the_owner_role(): void
    {
        $owner = $this->user();
        $requester = $this->user();
        $artist = $this->artist();
        $this->member($this->organization($artist), $owner, 'owner');
        $claim = $this->claim($requester, $artist);

        $this->actingAs($owner)->post(route('backend.my-entities.review.approve', $claim), ['role' => 'owner'])->assertSessionHasErrors('role');

        $this->assertSame('pending', $claim->fresh()->status);
        $this->assertFalse(OrganizationMember::where('user_id', $requester->id)->exists());
    }

    public function test_owner_rejects_with_a_required_reason(): void
    {
        $owner = $this->user();
        $requester = $this->user();
        $artist = $this->artist();
        $this->member($this->organization($artist), $owner, 'owner');
        $claim = $this->claim($requester, $artist);

        $this->actingAs($owner)->post(route('backend.my-entities.review.reject', $claim), [])->assertSessionHasErrors('review_notes');
        $this->post(route('backend.my-entities.review.reject', $claim), ['review_notes' => 'No lo conocemos.'])->assertSessionHasNoErrors();

        $claim->refresh();
        $this->assertSame('rejected', $claim->status);
        $this->assertSame($owner->id, (int) $claim->reviewed_by);
        $this->assertFalse(OrganizationMember::where('user_id', $requester->id)->exists());
    }

    public function test_only_active_owners_of_that_ficha_can_review(): void
    {
        $artist = $this->artist();
        $org = $this->organization($artist);
        $claim = $this->claim($this->user(), $artist);

        $editor = $this->user();
        $this->member($org, $editor, 'editor');
        $inactiveOwner = $this->user();
        $this->member($org, $inactiveOwner, 'owner', 'inactive');
        $otherArtist = $this->artist();
        $otherOwner = $this->user();
        $this->member($this->organization($otherArtist), $otherOwner, 'owner');

        foreach ([$editor, $inactiveOwner, $otherOwner, $this->user(), $this->user('colaborador')] as $user) {
            $this->actingAs($user)->post(route('backend.my-entities.review.approve', $claim), ['role' => 'editor'])->assertForbidden();
            $this->post(route('backend.my-entities.review.reject', $claim), ['review_notes' => 'No.'])->assertForbidden();
            $this->get(route('backend.my-entities.index'))->assertDontSee('Solicitudes para mis fichas');
        }

        $this->assertSame('pending', $claim->fresh()->status);
    }

    public function test_not_delegable_cases(): void
    {
        $owner = $this->user();
        $artist = $this->artist();
        $org = $this->organization($artist);
        $this->member($org, $owner, 'owner');

        $own = $this->claim($owner, $artist);
        $creation = $this->claim($this->user(), $artist, ['kind' => 'create', 'entity_id' => null, 'proposed_name' => 'Otro']);
        $resolved = $this->claim($this->user(), $artist, ['status' => 'approved']);

        foreach ([$own, $creation, $resolved] as $claim) {
            $this->actingAs($owner)->post(route('backend.my-entities.review.approve', $claim), ['role' => 'editor'])->assertForbidden();
        }
    }

    public function test_inactive_organization_is_not_delegable(): void
    {
        $owner = $this->user();
        $artist = $this->artist();
        $this->member($this->organization($artist, 'inactive'), $owner, 'owner');
        $claim = $this->claim($this->user(), $artist);

        $this->actingAs($owner)->post(route('backend.my-entities.review.approve', $claim), ['role' => 'editor'])->assertForbidden();
    }

    public function test_an_existing_owner_is_not_downgraded(): void
    {
        $owner = $this->user();
        $coOwner = $this->user();
        $artist = $this->artist();
        $org = $this->organization($artist);
        $this->member($org, $owner, 'owner');
        $this->member($org, $coOwner, 'owner');
        $claim = $this->claim($coOwner, $artist);

        $this->actingAs($owner)->post(route('backend.my-entities.review.approve', $claim), ['role' => 'editor'])->assertSessionHasErrors('claim');

        $this->assertSame('owner', OrganizationMember::where('organization_id', $org->id)->where('user_id', $coOwner->id)->value('role'));
    }

    public function test_switch_off_disables_delegation_and_admin_keeps_working(): void
    {
        $owner = $this->user();
        $requester = $this->user();
        $artist = $this->artist();
        $this->member($this->organization($artist), $owner, 'owner');
        $claim = $this->claim($requester, $artist);

        config(['entity_management.claims.delegate_to_owners' => false]);
        $this->actingAs($owner)->post(route('backend.my-entities.review.approve', $claim), ['role' => 'editor'])->assertForbidden();
        $this->get(route('backend.my-entities.index'))->assertDontSee('Solicitudes para mis fichas');

        $this->actingAs($this->user('administrador'))->post(route('backend.claims.approve', $claim), ['role' => 'owner'])->assertSessionHasNoErrors();
        $this->assertSame('approved', $claim->fresh()->status);
    }
}
