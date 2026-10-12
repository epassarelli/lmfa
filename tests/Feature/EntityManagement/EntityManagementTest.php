<?php

namespace Tests\Feature\EntityManagement;

use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Services\OrganizationProvisioner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EntityManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role = 'colaborador'): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function interprete(string $slug, ?int $userId = null): Interprete
    {
        return Interprete::create([
            'interprete' => 'Artista '.$slug,
            'slug' => $slug,
            'biografia' => 'Biografía de prueba.',
            'estado' => 1,
            'user_id' => $userId ?? 1,
        ]);
    }

    private function festival(string $slug, ?int $userId = null): Festival
    {
        $mes = Mes::first() ?: Mes::create(['nombre' => 'Enero']);

        return Festival::forceCreate([
            'title' => 'Festival '.$slug,
            'slug' => $slug,
            'mes_id' => $mes->id,
            'user_id' => $userId ?? 1,
        ]);
    }

    private function organizationFor(Model|Interprete|Festival $entity, array $org = []): Organization
    {
        $organization = Organization::create(array_merge([
            'type' => 'artista',
            'name' => 'Org '.uniqid(),
            'status' => 'active',
        ], $org));

        OrganizationEntity::create([
            'organization_id' => $organization->id,
            'entity_type' => $entity->getMorphClass(),
            'entity_id' => $entity->getKey(),
            'is_primary' => true,
        ]);

        return $organization;
    }

    private function member(Organization $organization, User $user, string $role = 'editor', string $status = 'active'): void
    {
        OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => $status,
        ]);
        $user->forgetManagedEntities();
    }

    /** @test */
    public function active_editor_and_owner_can_manage_linked_entity(): void
    {
        $artist = $this->interprete('em-editor');
        $organization = $this->organizationFor($artist);
        $editor = $this->user();
        $owner = $this->user();
        $this->member($organization, $editor, 'editor');
        $this->member($organization, $owner, 'owner');

        $this->assertTrue($editor->canManage($artist));
        $this->assertTrue($owner->canManage($artist));
        $this->assertTrue($editor->can('update', $artist));
    }

    /** @test */
    public function member_role_pending_inactive_and_unlinked_do_not_grant_access(): void
    {
        $artist = $this->interprete('em-denied');
        $other = $this->interprete('em-other');
        $organization = $this->organizationFor($artist);

        $reader = $this->user();
        $pending = $this->user();
        $inactive = $this->user();
        $stranger = $this->user();
        $this->member($organization, $reader, 'member');
        $this->member($organization, $pending, 'editor', 'pending');
        $this->member($organization, $inactive, 'editor', 'inactive');

        $this->assertFalse($reader->canManage($artist));
        $this->assertFalse($pending->canManage($artist));
        $this->assertFalse($inactive->canManage($artist));
        $this->assertFalse($stranger->canManage($artist));

        $editor = $this->user();
        $this->member($organization, $editor, 'editor');
        $this->assertTrue($editor->canManage($artist));
        $this->assertFalse($editor->canManage($other), 'No debe poder editar una ficha no vinculada.');
    }

    /** @test */
    public function inactive_organization_does_not_grant_access(): void
    {
        $artist = $this->interprete('em-inactive-org');
        $organization = $this->organizationFor($artist, ['status' => 'inactive']);
        $editor = $this->user();
        $this->member($organization, $editor, 'editor');

        $this->assertFalse($editor->canManage($artist));
    }

    /** @test */
    public function several_users_can_administer_the_same_entity_and_one_user_several_entities(): void
    {
        $a = $this->interprete('em-multi-a');
        $b = $this->interprete('em-multi-b');
        $organization = $this->organizationFor($a);
        OrganizationEntity::create([
            'organization_id' => $organization->id,
            'entity_type' => $b->getMorphClass(),
            'entity_id' => $b->id,
        ]);
        $first = $this->user();
        $second = $this->user('prensa');
        $this->member($organization, $first);
        $this->member($organization, $second);

        $this->assertTrue($first->canManage($a));
        $this->assertTrue($second->canManage($a));
        $this->assertTrue($first->canManage($b));
    }

    /** @test */
    public function administrator_and_legacy_creator_keep_access(): void
    {
        $admin = $this->user('administrador');
        $creator = $this->user();
        $mine = $this->interprete('em-legacy', $creator->id);
        $notMine = $this->interprete('em-not-mine');
        $festival = $this->festival('em-legacy-fest', $creator->id);

        $this->assertTrue($admin->canManage($notMine));
        $this->assertTrue($creator->canManage($mine));
        $this->assertTrue($creator->can('update', $festival));
        $this->assertFalse($creator->canManage($notMine));
    }

    /** @test */
    public function scope_returns_created_and_managed_entities_without_per_row_queries(): void
    {
        $user = $this->user();
        $created = $this->interprete('em-scope-created', $user->id);
        $managed = $this->interprete('em-scope-managed');
        $this->interprete('em-scope-other');
        $this->member($this->organizationFor($managed), $user);

        DB::enableQueryLog();
        $ids = Interprete::managedOrLegacyBy($user)->pluck('interpretes.id')->all();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertEqualsCanonicalizing([$created->id, $managed->id], $ids);
        $this->assertSame(1, $queries);
    }

    /** @test */
    public function interprete_listing_is_scoped_only_when_access_by_entities_is_enabled(): void
    {
        $user = $this->user();
        $managed = $this->interprete('em-list-managed');
        $other = $this->interprete('em-list-other');
        $this->member($this->organizationFor($managed), $user);
        $this->actingAs($user);

        config(['backoffice_access.enabled' => false]);
        $this->get(route('backend.interpretes.index', ['search' => 'em-list']))
            ->assertOk()
            ->assertSee($managed->interprete)
            ->assertSee($other->interprete);

        config(['backoffice_access.enabled' => true]);
        $this->get(route('backend.interpretes.index', ['search' => 'Artista em-list']))
            ->assertOk()
            ->assertSee($managed->interprete)
            ->assertDontSee($other->interprete);
    }

    /** @test */
    public function festival_listing_includes_managed_festivals_for_collaborators(): void
    {
        $user = $this->user();
        $managed = $this->festival('em-fest-managed');
        $other = $this->festival('em-fest-other');
        $this->member($this->organizationFor($managed, ['type' => 'festival']), $user);
        $this->actingAs($user);

        $this->get(route('backend.festivales.index'))
            ->assertOk()
            ->assertSee($managed->title)
            ->assertDontSee($other->title);
    }

    /** @test */
    public function provisioner_is_idempotent_and_adds_owner(): void
    {
        $user = $this->user();
        $artist = $this->interprete('em-provision');

        $first = app(OrganizationProvisioner::class)->forEntity($artist, $user);
        $second = app(OrganizationProvisioner::class)->forEntity($artist, $user);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('artista', $first->type);
        $this->assertSame(1, OrganizationEntity::where('entity_id', $artist->id)
            ->where('entity_type', $artist->getMorphClass())->count());
        $this->assertSame(1, OrganizationMember::where('organization_id', $first->id)->count());
        $this->assertTrue($user->canManage($artist));
    }

    /** @test */
    public function provisioner_generates_unique_slugs_for_same_name(): void
    {
        $a = $this->interprete('em-slug-a');
        $b = $this->interprete('em-slug-b');
        $b->forceFill(['interprete' => $a->interprete])->save();

        $orgA = app(OrganizationProvisioner::class)->forEntity($a);
        $orgB = app(OrganizationProvisioner::class)->forEntity($b);

        $this->assertNotSame($orgA->slug, $orgB->slug);
    }

    /** @test */
    public function unsubscribed_or_unconsented_organizations_are_not_contactable(): void
    {
        $ok = Organization::create(['type' => 'artista', 'name' => 'Ok '.uniqid(), 'status' => 'active', 'contact_consent_at' => now()]);
        $unsub = Organization::create(['type' => 'artista', 'name' => 'Baja '.uniqid(), 'status' => 'active', 'contact_consent_at' => now(), 'unsubscribed_at' => now()]);
        $none = Organization::create(['type' => 'artista', 'name' => 'Sin '.uniqid(), 'status' => 'active']);

        $ids = Organization::contactable()->pluck('id')->all();

        $this->assertContains($ok->id, $ids);
        $this->assertNotContains($unsub->id, $ids);
        $this->assertNotContains($none->id, $ids);
    }
}
