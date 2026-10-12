<?php

namespace Tests\Feature\EntityManagement;

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

class OrganizationAdminTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role = 'colaborador'): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function admin(): User
    {
        return $this->user('administrador');
    }

    private function organization(array $attributes = []): Organization
    {
        return Organization::create(array_merge([
            'type' => 'artista',
            'name' => 'Org '.uniqid(),
            'status' => 'active',
        ], $attributes));
    }

    private function interprete(string $slug): Interprete
    {
        return Interprete::create([
            'interprete' => 'Artista '.$slug,
            'slug' => $slug,
            'biografia' => 'Biografía de prueba.',
            'estado' => 1,
            'user_id' => $this->baseUserId(),
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'productora',
            'name' => 'Productora '.uniqid(),
            'status' => 'active',
            'email' => 'contacto@example.test',
        ], $overrides);
    }

    /** @test */
    public function only_administrators_can_access_organizations(): void
    {
        $this->get(route('backend.organizations.index'))->assertRedirect();

        $this->actingAs($this->user('colaborador'));
        $this->get(route('backend.organizations.index'))->assertForbidden();
        $this->get(route('backend.organizations.create'))->assertForbidden();
        $this->get(route('backend.organization-lookups.users', ['q' => 'ab']))->assertForbidden();

        $this->actingAs($this->admin());
        $this->get(route('backend.organizations.index'))->assertOk();
    }

    /** @test */
    public function listing_filters_by_type_status_and_search(): void
    {
        $this->actingAs($this->admin());
        $tag = 'oa'.substr(uniqid(), -6);
        $this->organization(['type' => 'productora', 'name' => "Prod $tag"]);
        $this->organization(['type' => 'artista', 'name' => "Art $tag"]);
        $this->organization(['type' => 'productora', 'name' => "Inact $tag", 'status' => 'inactive']);

        $this->get(route('backend.organizations.index', ['search' => $tag, 'type' => 'productora', 'status' => 'active']))
            ->assertOk()
            ->assertSee("Prod $tag")
            ->assertDontSee("Art $tag")
            ->assertDontSee("Inact $tag");
    }

    /** @test */
    public function admin_can_create_and_update_an_organization_with_flags(): void
    {
        $admin = $this->actingAs($this->admin())->app['auth']->user();

        $payload = $this->validPayload([
            'contact_consent' => '1',
            'contact_consent_purpose' => 'novedades',
            'is_verified' => '1',
            'verification_method' => 'email_match',
        ]);

        $response = $this->post(route('backend.organizations.store'), $payload);
        $organization = Organization::where('name', $payload['name'])->firstOrFail();

        $response->assertRedirect(route('backend.organizations.edit', $organization));
        $this->assertNotNull($organization->slug);
        $this->assertNotNull($organization->contact_consent_at);
        $this->assertTrue($organization->is_verified);
        $this->assertSame($admin->id, (int) $organization->verified_by);
        $this->assertSame($admin->id, (int) $organization->created_by);

        $consentAt = $organization->contact_consent_at;

        $this->put(route('backend.organizations.update', $organization), $payload + ['unsubscribed' => '1'])
            ->assertRedirect(route('backend.organizations.edit', $organization));

        $organization->refresh();
        $this->assertNotNull($organization->unsubscribed_at);
        $this->assertSame($consentAt->timestamp, $organization->contact_consent_at->timestamp, 'La fecha original del consentimiento se conserva.');
        $this->assertFalse(Organization::contactable()->whereKey($organization->id)->exists());
    }

    /** @test */
    public function organization_validation_rejects_duplicate_slug_and_unknown_type(): void
    {
        $this->actingAs($this->admin());
        $existing = $this->organization(['slug' => 'slug-existente-'.uniqid()]);

        $this->post(route('backend.organizations.store'), $this->validPayload(['slug' => $existing->slug]))
            ->assertSessionHasErrors('slug');
        $this->post(route('backend.organizations.store'), $this->validPayload(['type' => 'inventado']))
            ->assertSessionHasErrors('type');
    }

    /** @test */
    public function empty_slug_is_generated_unique_from_the_name(): void
    {
        $this->actingAs($this->admin());
        $name = 'Mismo Nombre '.uniqid();

        $this->post(route('backend.organizations.store'), $this->validPayload(['name' => $name]));
        $this->post(route('backend.organizations.store'), $this->validPayload(['name' => $name]));

        $slugs = Organization::where('name', $name)->pluck('slug');
        $this->assertCount(2, $slugs);
        $this->assertCount(2, $slugs->unique());
    }

    /** @test */
    public function deactivating_an_organization_revokes_member_permissions(): void
    {
        $this->actingAs($this->admin());
        $artist = $this->interprete('oa-deactivate');
        $organization = $this->organization();
        OrganizationEntity::create(['organization_id' => $organization->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id]);
        $editor = $this->user();
        OrganizationMember::create(['organization_id' => $organization->id, 'user_id' => $editor->id, 'role' => 'editor', 'status' => 'active']);

        $this->assertTrue($editor->fresh()->canManage($artist));

        $this->put(route('backend.organizations.update', $organization), $this->validPayload([
            'type' => $organization->type,
            'name' => $organization->name,
            'status' => 'inactive',
        ]))->assertRedirect();

        $this->assertFalse($editor->fresh()->canManage($artist));
    }

    /** @test */
    public function admin_can_link_unlink_and_set_primary_entities(): void
    {
        $this->actingAs($this->admin());
        $artist = $this->interprete('oa-link');
        $organization = $this->organization();
        $other = $this->organization();

        $this->post(route('backend.organizations.entities.store', $organization), ['entity_type' => 'interprete', 'entity_id' => $artist->id])
            ->assertSessionHasNoErrors();
        $this->post(route('backend.organizations.entities.store', $organization), ['entity_type' => 'interprete', 'entity_id' => $artist->id]);

        $this->assertSame(1, OrganizationEntity::where('organization_id', $organization->id)->where('entity_id', $artist->id)->count());

        $this->post(route('backend.organizations.entities.store', $other), ['entity_type' => 'interprete', 'entity_id' => $artist->id, 'is_primary' => '1']);
        $link = OrganizationEntity::where('organization_id', $organization->id)->firstOrFail();
        $otherLink = OrganizationEntity::where('organization_id', $other->id)->firstOrFail();
        $this->assertTrue($otherLink->is_primary);

        $this->patch(route('backend.organizations.entities.primary', [$organization, $link]))->assertRedirect();
        $this->assertTrue($link->fresh()->is_primary);
        $this->assertFalse($otherLink->fresh()->is_primary, 'Una ficha tiene una sola organización principal.');

        $this->delete(route('backend.organizations.entities.destroy', [$organization, $link]))->assertRedirect();
        $this->assertDatabaseMissing('organization_entities', ['id' => $link->id]);
    }

    /** @test */
    public function entity_link_validation_and_scoped_bindings(): void
    {
        $this->actingAs($this->admin());
        $organization = $this->organization();
        $other = $this->organization();
        $artist = $this->interprete('oa-validate');

        $this->post(route('backend.organizations.entities.store', $organization), ['entity_type' => 'noticia', 'entity_id' => 1])
            ->assertSessionHasErrors('entity_type');
        $this->post(route('backend.organizations.entities.store', $organization), ['entity_type' => 'interprete', 'entity_id' => 999999999])
            ->assertSessionHasErrors('entity_id');

        $foreignLink = OrganizationEntity::create(['organization_id' => $other->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id]);
        $this->delete(route('backend.organizations.entities.destroy', [$organization, $foreignLink]))->assertNotFound();
        $this->assertDatabaseHas('organization_entities', ['id' => $foreignLink->id]);
    }

    /** @test */
    public function festivals_can_be_linked_too(): void
    {
        $this->actingAs($this->admin());
        $mes = Mes::first() ?: Mes::create(['nombre' => 'Enero']);
        $festival = Festival::forceCreate(['title' => 'Festival oa', 'slug' => 'festival-oa-'.uniqid(), 'mes_id' => $mes->id, 'user_id' => $this->baseUserId()]);
        $organization = $this->organization(['type' => 'festival']);

        $this->post(route('backend.organizations.entities.store', $organization), ['entity_type' => 'festival', 'entity_id' => $festival->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('organization_entities', [
            'organization_id' => $organization->id,
            'entity_type' => Festival::class,
            'entity_id' => $festival->id,
        ]);
    }

    /** @test */
    public function members_can_be_added_approved_and_removed(): void
    {
        $this->actingAs($this->admin());
        $artist = $this->interprete('oa-members');
        $organization = $this->organization();
        OrganizationEntity::create(['organization_id' => $organization->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id]);
        $user = $this->user();

        $this->post(route('backend.organizations.members.store', $organization), ['user_id' => $user->id, 'role' => 'editor', 'status' => 'pending'])
            ->assertSessionHasNoErrors();
        $member = OrganizationMember::where('organization_id', $organization->id)->where('user_id', $user->id)->firstOrFail();
        $this->assertFalse($user->fresh()->canManage($artist), 'Pendiente no otorga permisos.');

        $this->post(route('backend.organizations.members.store', $organization), ['user_id' => $user->id, 'role' => 'editor', 'status' => 'active'])
            ->assertSessionHasErrors('user_id');
        $this->assertSame(1, OrganizationMember::where('organization_id', $organization->id)->count());

        $this->patch(route('backend.organizations.members.update', [$organization, $member]), ['role' => 'editor', 'status' => 'active'])
            ->assertRedirect();
        $this->assertTrue($user->fresh()->canManage($artist), 'Aprobar el miembro habilita la edición.');

        $this->patch(route('backend.organizations.members.update', [$organization, $member]), ['role' => 'member', 'status' => 'active']);
        $this->assertFalse($user->fresh()->canManage($artist), 'El rol member es solo lectura.');

        $this->delete(route('backend.organizations.members.destroy', [$organization, $member]))->assertRedirect();
        $this->assertDatabaseMissing('organization_members', ['id' => $member->id]);
    }

    /** @test */
    public function member_validation_rejects_invalid_role_status_and_user(): void
    {
        $this->actingAs($this->admin());
        $organization = $this->organization();
        $user = $this->user();

        $this->post(route('backend.organizations.members.store', $organization), ['user_id' => $user->id, 'role' => 'jefe', 'status' => 'active'])
            ->assertSessionHasErrors('role');
        $this->post(route('backend.organizations.members.store', $organization), ['user_id' => 999999999, 'role' => 'editor', 'status' => 'active'])
            ->assertSessionHasErrors('user_id');
    }

    /** @test */
    public function lookups_require_two_characters_and_limit_results(): void
    {
        $this->actingAs($this->admin());
        $artist = $this->interprete('oa-lookup');
        $user = User::factory()->create(['name' => 'Lookup Persona Unica', 'email' => 'lookup.unica@example.test']);

        $this->getJson(route('backend.organization-lookups.entities', ['type' => 'interprete', 'q' => 'a']))
            ->assertOk()->assertExactJson(['results' => []]);
        $this->getJson(route('backend.organization-lookups.entities', ['type' => 'inventado', 'q' => 'Artista']))
            ->assertOk()->assertExactJson(['results' => []]);
        $this->getJson(route('backend.organization-lookups.entities', ['type' => 'interprete', 'q' => 'Artista oa-lookup']))
            ->assertOk()->assertJsonFragment(['id' => $artist->id]);

        $this->getJson(route('backend.organization-lookups.users', ['q' => 'lookup.unica']))
            ->assertOk()->assertJsonFragment(['id' => $user->id]);

        $response = $this->getJson(route('backend.organization-lookups.entities', ['type' => 'interprete', 'q' => 'ar']));
        $this->assertLessThanOrEqual(20, count($response->json('results')));
    }

    /** @test */
    public function edit_page_renders_links_and_members(): void
    {
        $this->actingAs($this->admin());
        $artist = $this->interprete('oa-edit');
        $organization = $this->organization(['name' => 'Org Visible '.uniqid()]);
        OrganizationEntity::create(['organization_id' => $organization->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id, 'is_primary' => true]);
        $member = User::factory()->create(['name' => 'Miembro Visible']);
        OrganizationMember::create(['organization_id' => $organization->id, 'user_id' => $member->id, 'role' => 'owner', 'status' => 'active']);

        $this->get(route('backend.organizations.edit', $organization))
            ->assertOk()
            ->assertSee($organization->name)
            ->assertSee($artist->interprete)
            ->assertSee('Miembro Visible')
            ->assertSee('Principal');
    }
}
