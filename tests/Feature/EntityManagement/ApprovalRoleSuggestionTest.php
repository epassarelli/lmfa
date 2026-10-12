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

class ApprovalRoleSuggestionTest extends TestCase
{
    use DatabaseTransactions;

    private function user(?string $role = null): User
    {
        $user = User::factory()->create();

        if ($role) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }

        return $user;
    }

    private function artist(string $name = null): Interprete
    {
        $name ??= 'Artista '.uniqid();

        return Interprete::create(['interprete' => $name, 'slug' => 'a-'.uniqid(), 'biografia' => 'Bio de prueba.', 'estado' => 1, 'user_id' => $this->baseUserId()]);
    }

    private function organizationFor(Interprete $artist, string $status = 'active', ?User $owner = null): Organization
    {
        $org = Organization::create(['type' => 'artista', 'name' => 'Org '.uniqid(), 'status' => $status]);
        OrganizationEntity::create(['organization_id' => $org->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id, 'is_primary' => true]);

        if ($owner) {
            OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $owner->id, 'role' => 'owner', 'status' => 'active']);
        }

        return $org;
    }

    private function claim(User $requester, Interprete $artist, string $relationship): EntityClaimRequest
    {
        return EntityClaimRequest::create([
            'user_id' => $requester->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id,
            'relationship' => $relationship, 'message' => 'Mensaje de prueba para la solicitud.', 'status' => 'pending',
        ]);
    }

    private function show(EntityClaimRequest $claim)
    {
        return $this->actingAs($this->user('administrador'))->get(route('backend.claims.show', $claim))->assertOk();
    }

    private function assertSuggested($response, string $role): void
    {
        $this->assertMatchesRegularExpression('/<option value="'.$role.'"\s+selected/', $response->getContent());
    }

    public function test_artist_like_relationships_suggest_owner_when_the_ficha_has_no_active_owner(): void
    {
        foreach (['artist', 'representative', 'organizer'] as $relationship) {
            $artist = $this->artist();
            $this->organizationFor($artist);
            $response = $this->show($this->claim($this->user(), $artist, $relationship));

            $this->assertSuggested($response, 'owner');
            $response->assertSee('se sugiere Responsable (owner)');
        }
    }

    public function test_press_and_other_suggest_editor_and_warn_when_there_is_no_owner(): void
    {
        foreach (['press', 'other'] as $relationship) {
            $response = $this->show($this->claim($this->user(), $this->artist(), $relationship));

            $this->assertSuggested($response, 'editor');
            $response->assertSee('confirmá quién debería controlarla', false);
        }
    }

    public function test_existing_active_owner_downgrades_the_suggestion_and_is_named(): void
    {
        $artist = $this->artist();
        $owner = User::factory()->create(['name' => 'Responsable Actual']);
        $this->organizationFor($artist, 'active', $owner);

        $response = $this->show($this->claim($this->user(), $artist, 'artist'));

        $this->assertSuggested($response, 'editor');
        $response->assertSee('por eso se sugiere Editor')->assertSee('La ficha ya tiene un responsable activo: Responsable Actual');
    }

    public function test_inactive_organization_raises_a_danger_alert(): void
    {
        $artist = $this->artist();
        $this->organizationFor($artist, 'inactive');

        $this->show($this->claim($this->user(), $artist, 'artist'))->assertSee('está inactiva: la aprobación se va a rechazar');
    }

    public function test_requester_that_is_already_a_member_gets_a_warning(): void
    {
        $artist = $this->artist();
        $requester = $this->user();
        $org = $this->organizationFor($artist);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $requester->id, 'role' => 'member', 'status' => 'inactive']);

        $this->show($this->claim($requester, $artist, 'artist'))->assertSee('ya es miembro de la organización (rol member, estado inactive)');
    }

    public function test_creation_requests_warn_about_missing_evidence_and_similar_names(): void
    {
        $this->artist('Los Cantores del Valle');
        $requester = $this->user();
        $claim = EntityClaimRequest::create([
            'user_id' => $requester->id, 'kind' => 'create', 'entity_type' => (new Interprete)->getMorphClass(), 'entity_id' => null,
            'proposed_name' => 'Cantores del Valle', 'relationship' => 'artist', 'message' => 'Mensaje de prueba para la solicitud.', 'status' => 'pending',
        ]);

        $response = $this->show($claim);

        $this->assertSuggested($response, 'owner');
        $response->assertSee('No incluyó link de respaldo')->assertSee('ficha(s) con nombre parecido');
    }

    public function test_the_suggestion_does_not_change_what_is_approved(): void
    {
        $artist = $this->artist();
        $requester = $this->user();
        $claim = $this->claim($requester, $artist, 'artist');

        $this->actingAs($this->user('administrador'))->post(route('backend.claims.approve', $claim), ['role' => 'member'])->assertSessionHasNoErrors();

        $this->assertSame('member', OrganizationMember::where('user_id', $requester->id)->firstOrFail()->role);
    }
}
