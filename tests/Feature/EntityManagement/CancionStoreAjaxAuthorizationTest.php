<?php

namespace Tests\Feature\EntityManagement;

use App\Models\Cancion;
use App\Models\Interprete;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CancionStoreAjaxAuthorizationTest extends TestCase
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

    private function artist(): Interprete
    {
        return Interprete::create(['interprete' => 'Artista '.uniqid(), 'slug' => 'a-'.uniqid(), 'biografia' => 'Bio de prueba.', 'estado' => 1, 'user_id' => $this->baseUserId()]);
    }

    private function link(Interprete $artist, User $user): void
    {
        $org = Organization::create(['type' => 'artista', 'name' => 'Org '.uniqid(), 'status' => 'active']);
        OrganizationEntity::create(['organization_id' => $org->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id, 'is_primary' => true]);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => 'editor', 'status' => 'active']);
    }

    private function addSong(User $user, Interprete $artist, string $name = null)
    {
        return $this->actingAs($user)->postJson(route('backend.canciones.store-ajax'), ['cancion' => $name ?? 'Tema '.uniqid(), 'interprete_id' => $artist->id]);
    }

    public function test_a_user_without_permissions_or_links_cannot_create_songs(): void
    {
        $artist = $this->artist();
        $name = 'Tema bloqueado '.uniqid();

        $this->addSong($this->user(), $artist, $name)->assertForbidden();
        $this->assertSame(0, Cancion::where('cancion', $name)->count());
    }

    public function test_collaborator_creates_pending_songs_and_administrator_publishes(): void
    {
        $artist = $this->artist();

        $this->addSong($this->user('colaborador'), $artist)->assertOk()->assertJson(['success' => true]);
        $this->assertSame(0, (int) Cancion::where('interprete_id', $artist->id)->latest('id')->first()->estado);

        $this->addSong($this->user('administrador'), $artist)->assertOk();
        $this->assertSame(1, (int) Cancion::where('interprete_id', $artist->id)->latest('id')->first()->estado);
    }

    public function test_verified_collaborator_publishes_directly(): void
    {
        Role::findOrCreate('verificado', 'web')->givePermissionTo(Permission::findOrCreate('publish contents', 'web'));
        $user = $this->user('colaborador');
        $user->assignRole('verificado');
        $artist = $this->artist();

        $this->addSong($user, $artist)->assertOk();

        $this->assertSame(1, (int) Cancion::where('interprete_id', $artist->id)->latest('id')->first()->estado);
    }

    public function test_a_member_without_global_permission_only_uses_their_own_artists(): void
    {
        $user = $this->user();
        $mine = $this->artist();
        $foreign = $this->artist();
        $this->link($mine, $user);

        $this->addSong($user, $mine)->assertOk();
        $this->assertSame(0, (int) Cancion::where('interprete_id', $mine->id)->latest('id')->first()->estado);

        $this->addSong($user, $foreign)->assertForbidden();
        $this->assertSame(0, Cancion::where('interprete_id', $foreign->id)->count());
    }
}
