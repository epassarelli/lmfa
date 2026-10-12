<?php

namespace Tests\Feature\EntityManagement;

use App\Models\EntityClaimRequest;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EntityCreationRequestTest extends TestCase
{
    use DatabaseTransactions;

    private function user(?string $role = 'colaborador'): User
    {
        $user = User::factory()->create();

        if ($role) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }

        return $user;
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'entity_type' => 'interprete',
            'proposed_name' => 'Grupo Nuevo '.uniqid(),
            'relationship' => 'artist',
            'message' => 'Soy el cantante y pueden verificarlo por Instagram.',
            'evidence_url' => 'https://instagram.example.test/gruponuevo',
        ], $extra);
    }

    private function mes(): Mes
    {
        return Mes::first() ?: Mes::create(['nombre' => 'Enero']);
    }

    private function claimOf(User $user): EntityClaimRequest
    {
        return EntityClaimRequest::where('user_id', $user->id)->latest('id')->firstOrFail();
    }

    public function test_user_without_fichas_can_request_creation(): void
    {
        $user = $this->user();
        $data = $this->payload();

        $this->actingAs($user)->post(route('backend.my-entities.creations.store'), $data)->assertSessionHasNoErrors()->assertRedirect(route('backend.my-entities.index'));

        $claim = $this->claimOf($user);
        $this->assertSame('create', $claim->kind);
        $this->assertSame('pending', $claim->status);
        $this->assertNull($claim->entity_id);
        $this->assertSame($data['proposed_name'], $claim->proposed_name);
        $this->assertSame($data['evidence_url'], $claim->evidence_url);
        $this->assertSame(0, Interprete::where('interprete', $data['proposed_name'])->count());
    }

    public function test_validation_and_abuse_limits(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $this->actingAs($this->user())->get(route('backend.my-entities.index'))->assertOk();
        $url = route('backend.my-entities.creations.store');

        $this->post($url, $this->payload(['message' => 'corto']))->assertSessionHasErrors('message');
        $this->post($url, $this->payload(['evidence_url' => 'no-es-un-link']))->assertSessionHasErrors('evidence_url');
        $this->post($url, $this->payload(['entity_type' => 'festival']))->assertSessionHasErrors('mes_id');
        $this->post($url, $this->payload(['entity_type' => 'peña']))->assertSessionHasErrors('entity_type');

        $same = $this->payload();
        $this->post($url, $same)->assertSessionHasNoErrors();
        $this->post($url, $same)->assertSessionHasErrors('proposed_name');

        for ($i = 0; $i < 4; $i++) {
            $this->post($url, $this->payload())->assertSessionHasNoErrors();
        }
        $this->post($url, $this->payload())->assertSessionHasErrors('entity_id');
    }

    public function test_similar_endpoint_suggests_existing_entities(): void
    {
        $existing = Interprete::create(['interprete' => 'Los Cantores del Alba', 'slug' => 'cantores-alba-'.uniqid(), 'biografia' => 'Bio.', 'estado' => 1, 'user_id' => 1]);

        $this->actingAs($this->user())
            ->getJson(route('backend.my-entities.similar', ['type' => 'interprete', 'q' => 'Cantores del Alba']))
            ->assertOk()
            ->assertJsonFragment(['id' => $existing->id, 'name' => 'Los Cantores del Alba']);

        $this->getJson(route('backend.my-entities.similar', ['type' => 'interprete', 'q' => 'ab']))->assertOk()->assertJson(['results' => []]);
        $this->getJson(route('backend.my-entities.similar', ['type' => 'inexistente', 'q' => 'Cantores']))->assertStatus(422);
    }

    public function test_approval_creates_unpublished_stub_with_membership_and_unlocks_sections(): void
    {
        config(['backoffice_access.enabled' => true]);
        $user = $this->user();
        $admin = $this->user('administrador');
        $data = $this->payload();
        $this->actingAs($user)->post(route('backend.my-entities.creations.store'), $data);
        $claim = $this->claimOf($user);

        $this->get(route('backend.discos.index'))->assertRedirect(route('backend.my-entities.index'));

        $this->actingAs($admin)->get(route('backend.claims.show', $claim))->assertOk()->assertSee($data['proposed_name']);
        $this->post(route('backend.claims.approve', $claim), ['role' => 'owner'])->assertSessionHasNoErrors();

        $claim->refresh();
        $artist = Interprete::findOrFail($claim->entity_id);
        $this->assertSame('approved', $claim->status);
        $this->assertSame($data['proposed_name'], $artist->interprete);
        $this->assertSame(0, (int) $artist->estado);
        $this->assertSame('', (string) $artist->biografia);
        $this->assertSame($user->id, (int) $artist->user_id);
        $this->assertTrue(OrganizationMember::where('user_id', $user->id)->where('role', 'owner')->where('status', 'active')->exists());

        $this->actingAs(User::find($user->id))->get(route('backend.discos.index'))->assertOk();
    }

    public function test_admin_can_link_to_an_existing_duplicate_instead_of_creating(): void
    {
        $user = $this->user();
        $admin = $this->user('administrador');
        $existing = Interprete::create(['interprete' => 'Duplicado Existente', 'slug' => 'duplicado-'.uniqid(), 'biografia' => 'Bio.', 'estado' => 1, 'user_id' => 1]);
        $this->actingAs($user)->post(route('backend.my-entities.creations.store'), $this->payload(['proposed_name' => 'Duplicado Existente']));
        $claim = $this->claimOf($user);
        $before = Interprete::count();

        $this->actingAs($admin)->post(route('backend.claims.approve', $claim), ['role' => 'editor', 'existing_entity_id' => $existing->id])->assertSessionHasNoErrors();

        $this->assertSame($before, Interprete::count());
        $this->assertSame($existing->id, (int) $claim->fresh()->entity_id);
        $this->assertTrue(OrganizationMember::where('user_id', $user->id)->where('role', 'editor')->exists());
    }

    public function test_festival_creation_needs_a_month_and_creates_a_draft(): void
    {
        $user = $this->user();
        $admin = $this->user('administrador');
        $mes = $this->mes();
        $this->actingAs($user)->post(route('backend.my-entities.creations.store'), $this->payload(['entity_type' => 'festival', 'mes_id' => $mes->id, 'relationship' => 'organizer']))->assertSessionHasNoErrors();
        $claim = $this->claimOf($user);

        $this->actingAs($admin)->post(route('backend.claims.approve', $claim), ['role' => 'owner'])->assertSessionHasNoErrors();

        $festival = Festival::findOrFail($claim->fresh()->entity_id);
        $this->assertSame('draft', $festival->status);
        $this->assertSame($mes->id, (int) $festival->mes_id);
        $this->assertSame($user->id, (int) $festival->user_id);
    }

    public function test_rejection_creates_nothing_and_non_admins_cannot_review(): void
    {
        $user = $this->user();
        $admin = $this->user('administrador');
        $this->actingAs($user)->post(route('backend.my-entities.creations.store'), $this->payload());
        $claim = $this->claimOf($user);
        $before = Interprete::count();

        $this->post(route('backend.claims.approve', $claim), ['role' => 'owner'])->assertForbidden();

        $this->actingAs($admin)->post(route('backend.claims.reject', $claim), ['review_notes' => 'No pudimos verificarlo.'])->assertSessionHasNoErrors();

        $this->assertSame('rejected', $claim->fresh()->status);
        $this->assertSame($before, Interprete::count());
        $this->assertFalse(OrganizationMember::where('user_id', $user->id)->exists());
    }

    public function test_my_entities_lists_the_creation_request_and_shows_the_form(): void
    {
        $user = $this->user();
        $data = $this->payload();
        $this->actingAs($user)->post(route('backend.my-entities.creations.store'), $data);

        $this->get(route('backend.my-entities.index'))->assertOk()
            ->assertSee('Pedí que la creemos')
            ->assertSee($data['proposed_name'])
            ->assertSee('Alta');
    }
}
