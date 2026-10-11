<?php

namespace Tests\Feature\EntityManagement;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\Interprete;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemberContentAccessTest extends TestCase
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

    private function artist(string $slug): Interprete
    {
        return Interprete::create([
            'interprete' => 'Artista '.$slug.' '.uniqid(), 'slug' => $slug.'-'.uniqid(), 'biografia' => 'Biografía de prueba.', 'estado' => 1, 'user_id' => 1,
        ]);
    }

    private function link(Interprete $artist, User $user, string $role = 'editor', string $status = 'active', string $orgStatus = 'active'): void
    {
        $org = Organization::create(['type' => 'artista', 'name' => 'Org '.uniqid(), 'status' => $orgStatus]);
        OrganizationEntity::create(['organization_id' => $org->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id, 'is_primary' => true]);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => $role, 'status' => $status]);
    }

    private function album(Interprete $artist, array $extra = []): Album
    {
        return Album::create(array_merge(['album' => 'Disco '.uniqid(), 'slug' => 'disco-'.uniqid(), 'anio' => '2020', 'interprete_id' => $artist->id, 'user_id' => 1, 'estado' => 1, 'visitas' => 0], $extra));
    }

    private function song(Interprete $artist, array $extra = []): Cancion
    {
        return Cancion::create(array_merge(['cancion' => 'Tema '.uniqid(), 'slug' => 'tema-'.uniqid(), 'interprete_id' => $artist->id, 'user_id' => 1, 'estado' => 1, 'visitas' => 0], $extra));
    }

    private function albumPayload(Interprete $artist, array $extra = []): array
    {
        return array_merge(['album' => 'Nuevo disco '.uniqid(), 'anio' => '2024', 'interprete_id' => $artist->id], $extra);
    }

    private function songPayload(Interprete $artist, array $extra = []): array
    {
        return array_merge(['cancion' => 'Nuevo tema '.uniqid(), 'interprete_id' => $artist->id, 'letra' => '<p>Letra de prueba</p>'], $extra);
    }

    public function test_editor_creates_and_publishes_album_for_own_artist(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $this->link($mine, $user);

        $this->actingAs($user)->post(route('backend.discos.store'), $this->albumPayload($mine))->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(1, (int) Album::where('interprete_id', $mine->id)->latest('id')->first()->estado);
    }

    public function test_editor_cannot_create_album_for_another_artist(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $other = $this->artist('ajeno');
        $this->link($mine, $user);

        $this->actingAs($user)->post(route('backend.discos.store'), $this->albumPayload($other))->assertSessionHasErrors('interprete_id');
        $this->assertSame(0, Album::where('interprete_id', $other->id)->count());
    }

    public function test_editor_edits_own_album_but_not_foreign_one(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $other = $this->artist('ajeno');
        $this->link($mine, $user);
        $own = $this->album($mine);
        $foreign = $this->album($other);

        $this->actingAs($user)->get(route('backend.discos.edit', $own))->assertOk();
        $this->put(route('backend.discos.update', $own), $this->albumPayload($mine, ['album' => 'Renombrado']))->assertSessionHasNoErrors();
        $this->assertSame('Renombrado', $own->fresh()->album);
        $this->assertSame(1, (int) $own->fresh()->estado);

        $this->get(route('backend.discos.edit', $foreign))->assertForbidden();
        $this->put(route('backend.discos.update', $foreign), $this->albumPayload($other))->assertForbidden();
        $this->put(route('backend.discos.update', $own), $this->albumPayload($other))->assertSessionHasErrors('interprete_id');
    }

    public function test_editor_manages_songs_including_lyrics_for_own_artist_only(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $other = $this->artist('ajeno');
        $this->link($mine, $user);
        $foreign = $this->song($other);

        $this->actingAs($user)->post(route('backend.canciones.store'), $this->songPayload($mine))->assertSessionHasNoErrors()->assertRedirect();
        $created = Cancion::where('interprete_id', $mine->id)->latest('id')->first();
        $this->assertSame(1, (int) $created->estado);
        $this->assertStringContainsString('Letra de prueba', (string) $created->letra);

        $this->post(route('backend.canciones.store'), $this->songPayload($other))->assertSessionHasErrors('interprete_id');
        $this->get(route('backend.canciones.edit', $foreign))->assertForbidden();
    }

    public function test_member_role_and_inactive_links_get_no_write_access(): void
    {
        $artist = $this->artist('propio');
        $album = $this->album($artist);

        foreach ([['member', 'active', 'active'], ['editor', 'inactive', 'active'], ['editor', 'pending', 'active'], ['owner', 'active', 'inactive']] as [$role, $status, $orgStatus]) {
            $user = $this->user();
            $this->link($artist, $user, $role, $status, $orgStatus);

            $this->actingAs($user)->get(route('backend.discos.edit', $album))->assertForbidden();
            $this->post(route('backend.discos.store'), $this->albumPayload($artist))->assertForbidden();
        }
    }

    public function test_user_without_link_or_permissions_cannot_write(): void
    {
        $artist = $this->artist('propio');

        $this->actingAs($this->user())->post(route('backend.discos.store'), $this->albumPayload($artist))->assertForbidden();
        $this->post(route('backend.canciones.store'), $this->songPayload($artist))->assertForbidden();
    }

    public function test_listings_for_a_pure_editor_only_include_own_artist_content(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $other = $this->artist('ajeno');
        $this->link($mine, $user);
        $this->album($mine, ['album' => 'Disco visible propio']);
        $this->album($other, ['album' => 'Disco ajeno oculto']);
        $this->song($mine, ['cancion' => 'Tema visible propio']);
        $this->song($other, ['cancion' => 'Tema ajeno oculto']);

        $this->actingAs($user)->get(route('backend.discos.index'))->assertOk()->assertSee('Disco visible propio')->assertDontSee('Disco ajeno oculto');

        $rows = collect($this->getJson(route('backend.canciones.get'))->assertOk()->json('data'))->pluck('cancion');
        $this->assertTrue($rows->contains('Tema visible propio'));
        $this->assertFalse($rows->contains('Tema ajeno oculto'));
    }

    public function test_create_form_only_offers_own_artists_to_a_pure_editor(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $other = $this->artist('ajeno');
        $this->link($mine, $user);

        $this->actingAs($user)->get(route('backend.discos.create'))->assertOk()->assertSee($mine->interprete)->assertDontSee($other->interprete);
    }

    public function test_existing_roles_keep_their_access(): void
    {
        $artist = $this->artist('propio');
        $admin = $this->user('administrador');

        $this->actingAs($admin)->post(route('backend.discos.store'), $this->albumPayload($artist))->assertSessionHasNoErrors();
        $this->assertSame(1, (int) Album::where('interprete_id', $artist->id)->latest('id')->first()->estado);
    }
}
