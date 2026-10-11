<?php

namespace Tests\Feature\EntityManagement;

use App\Http\Middleware\EnsureBackofficeSection;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackofficeAccessByEntitiesTest extends TestCase
{
    use DatabaseTransactions;

    /** @var array<string, string> sección => ruta de listado */
    private const SECTIONS = [
        'interpretes' => 'backend.interpretes.index',
        'discos' => 'backend.discos.index',
        'canciones' => 'backend.canciones.index',
        'festivales' => 'backend.festivales.index',
        'eventos' => 'backend.events.index',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        config(['backoffice_access.enabled' => true]);
    }

    private function user(?string $role = 'colaborador'): User
    {
        $user = User::factory()->create();

        if ($role) {
            Role::findOrCreate($role, 'web');
            $user->assignRole($role);
        }

        return $user;
    }

    private function artist(?int $userId = 1): Interprete
    {
        return Interprete::create(['interprete' => 'Artista '.uniqid(), 'slug' => 'artista-'.uniqid(), 'biografia' => 'Bio de prueba.', 'estado' => 1, 'user_id' => $userId]);
    }

    private function festival(?int $userId = 1): Festival
    {
        $mes = Mes::first() ?: Mes::create(['nombre' => 'Enero']);

        return Festival::forceCreate(['title' => 'Festival '.uniqid(), 'slug' => 'festival-'.uniqid(), 'mes_id' => $mes->id, 'user_id' => $userId]);
    }

    private function link(Model $entity, User $user, string $role = 'editor', string $status = 'active', string $orgStatus = 'active'): void
    {
        $org = Organization::create(['type' => 'artista', 'name' => 'Org '.uniqid(), 'status' => $orgStatus]);
        OrganizationEntity::create(['organization_id' => $org->id, 'entity_type' => $entity->getMorphClass(), 'entity_id' => $entity->getKey(), 'is_primary' => true]);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => $role, 'status' => $status]);
    }

    private function assertBlocked(User $user, string $route): void
    {
        $this->actingAs($user)->get(route($route))
            ->assertRedirect(route('backend.my-entities.index'))
            ->assertSessionHas('warning', EnsureBackofficeSection::MESSAGE);
    }

    public function test_flag_off_keeps_current_behavior(): void
    {
        config(['backoffice_access.enabled' => false]);

        foreach (self::SECTIONS as $route) {
            $this->actingAs($this->user())->get(route($route))->assertOk();
        }
    }

    public function test_user_without_entities_is_redirected_from_entity_sections_with_notice(): void
    {
        foreach ([$this->user('colaborador'), $this->user('prensa'), $this->user(null)] as $user) {
            foreach (self::SECTIONS as $route) {
                $this->assertBlocked($user, $route);
            }
        }
    }

    public function test_blocked_json_requests_get_403_with_the_same_message(): void
    {
        $this->actingAs($this->user())
            ->getJson(route('backend.canciones.get'))
            ->assertForbidden()
            ->assertJson(['message' => EnsureBackofficeSection::MESSAGE]);
    }

    public function test_my_entities_stays_available_and_shows_banner_and_trimmed_sidebar(): void
    {
        $response = $this->actingAs($this->user())->followingRedirects()->get(route('backend.discos.index'));

        $response->assertOk()
            ->assertSee(EnsureBackofficeSection::MESSAGE)
            ->assertSee('Todavía no administrás ninguna ficha')
            ->assertSee('Mis fichas')
            ->assertDontSee('/admin/discos')
            ->assertDontSee('/admin/festivales')
            ->assertDontSee('/admin/interpretes')
            ->assertDontSee('/admin/canciones')
            ->assertDontSee('/admin/events');
    }

    public function test_sections_not_tied_to_entities_are_untouched_for_now(): void
    {
        $this->actingAs($this->user())->get(route('backend.mitos.index'))->assertOk();
        $this->get(route('backend.comidas.index'))->assertOk();
    }

    public function test_artist_membership_unlocks_artist_sections_only(): void
    {
        $user = $this->user();
        $this->link($this->artist(), $user);
        $this->actingAs($user);

        foreach (['interpretes', 'discos', 'canciones', 'eventos'] as $section) {
            $this->get(route(self::SECTIONS[$section]))->assertOk();
        }
        $this->assertBlocked($user, self::SECTIONS['festivales']);

        $this->get(route('backend.my-entities.index'))->assertOk()
            ->assertSee('/admin/discos')->assertSee('/admin/interpretes')->assertDontSee('/admin/festivales')
            ->assertDontSee('Todavía no administrás ninguna ficha');
    }

    public function test_festival_membership_unlocks_festivals_and_events_only(): void
    {
        $user = $this->user();
        $this->link($this->festival(), $user);
        $this->actingAs($user);

        foreach (['festivales', 'eventos'] as $section) {
            $this->get(route(self::SECTIONS[$section]))->assertOk();
        }
        foreach (['interpretes', 'discos', 'canciones'] as $section) {
            $this->assertBlocked($user, self::SECTIONS[$section]);
        }
    }

    public function test_legacy_creator_counts_as_having_the_entity(): void
    {
        $user = $this->user();
        $this->artist($user->id);

        $this->actingAs($user)->get(route('backend.discos.index'))->assertOk();
    }

    public function test_read_only_inactive_or_pending_links_do_not_unlock_sections(): void
    {
        $artist = $this->artist();

        foreach ([['member', 'active', 'active'], ['editor', 'inactive', 'active'], ['editor', 'pending', 'active'], ['owner', 'active', 'inactive']] as [$role, $status, $orgStatus]) {
            $user = $this->user();
            $this->link($artist, $user, $role, $status, $orgStatus);

            $this->assertBlocked($user, 'backend.discos.index');
        }
    }

    public function test_access_is_reflected_right_after_a_link_is_approved(): void
    {
        $user = $this->user();
        $artist = $this->artist();
        $this->assertBlocked($user, 'backend.discos.index');

        $this->link($artist, $user);

        $this->actingAs(User::find($user->id))->get(route('backend.discos.index'))->assertOk();
    }

    public function test_administrator_is_never_restricted(): void
    {
        $admin = $this->user('administrador');

        foreach (self::SECTIONS as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }
}
