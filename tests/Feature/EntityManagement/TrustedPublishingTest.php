<?php

namespace Tests\Feature\EntityManagement;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\Categoria;
use App\Models\Event;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\News;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TrustedPublishingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['responsecache.enabled' => false, 'responsecache.cache_store' => 'array']);
        Queue::fake();
        Http::fake();
        Storage::fake('public');
        Storage::fake('local');

        // Mismo estado que deja la migracion: permiso y rol de confianza.
        Role::findOrCreate('verificado', 'web')->givePermissionTo(Permission::findOrCreate('publish contents', 'web'));
    }

    private function collaborator(bool $verified = false): User
    {
        Role::findOrCreate('colaborador', 'web');
        $user = User::factory()->create();
        $user->assignRole('colaborador');

        if ($verified) {
            $user->assignRole('verificado');
        }

        return $user;
    }

    private function artist(string $slug = 'a'): Interprete
    {
        return Interprete::create(['interprete' => 'Artista '.$slug.' '.uniqid(), 'slug' => $slug.'-'.uniqid(), 'biografia' => 'Bio de prueba.', 'estado' => 1, 'user_id' => 1]);
    }

    private function link(Interprete $artist, User $user): void
    {
        $org = Organization::create(['type' => 'artista', 'name' => 'Org '.uniqid(), 'status' => 'active']);
        OrganizationEntity::create(['organization_id' => $org->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id, 'is_primary' => true]);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => 'editor', 'status' => 'active']);
    }

    private function albumPayload(Interprete $artist, array $extra = []): array
    {
        return array_merge(['album' => 'Disco '.uniqid(), 'anio' => '2024', 'interprete_id' => $artist->id], $extra);
    }

    private function album(Interprete $artist, int $userId, array $extra = []): Album
    {
        return Album::create(array_merge(['album' => 'Disco '.uniqid(), 'slug' => 'disco-'.uniqid(), 'anio' => '2020', 'interprete_id' => $artist->id, 'user_id' => $userId, 'estado' => 1, 'visitas' => 0], $extra));
    }

    // ---------------- Camino de confianza ----------------

    public function test_verified_publishes_directly_and_plain_collaborator_goes_to_review(): void
    {
        $artist = $this->artist();
        $verified = $this->collaborator(true);
        $plain = $this->collaborator();
        $this->link($artist, $verified);
        $this->link($artist, $plain);

        $a = $this->albumPayload($artist);
        $this->actingAs($verified)->post(route('backend.discos.store'), $a)->assertSessionHasNoErrors();
        $this->assertSame(1, (int) Album::where('album', $a['album'])->firstOrFail()->estado);

        $b = $this->albumPayload($artist);
        $this->actingAs($plain)->post(route('backend.discos.store'), $b)->assertSessionHasNoErrors();
        $this->assertSame(0, (int) Album::where('album', $b['album'])->firstOrFail()->estado);

        $song = ['cancion' => 'Tema '.uniqid(), 'interprete_id' => $artist->id, 'letra' => '<p>Letra</p>'];
        $this->actingAs($verified)->post(route('backend.canciones.store'), $song)->assertSessionHasNoErrors();
        $this->assertSame(1, (int) Cancion::where('cancion', $song['cancion'])->firstOrFail()->estado);
    }

    public function test_verified_publishes_events_and_news_directly(): void
    {
        $artist = $this->artist();
        $verified = $this->collaborator(true);
        $this->link($artist, $verified);

        $event = ['event_format' => 'single', 'title' => 'Evento '.uniqid(), 'body' => '<p>Detalle</p>', 'start_at' => now()->addWeek()->format('Y-m-d H:i:s'), 'interprete_ids' => [$artist->id], 'estado' => 1];
        $this->actingAs($verified)->post(route('backend.events.store'), $event)->assertSessionHasNoErrors();
        $this->assertSame('published', Event::where('title', $event['title'])->firstOrFail()->editorial_status);

        $title = 'Noticia '.uniqid();
        $news = [
            'titulo' => $title, 'slug' => Str::slug($title), 'noticia' => '<p>Cuerpo</p>', 'foto' => UploadedFile::fake()->image('n.jpg'),
            'interprete_principal_id' => $artist->id, 'categoria_id' => (Categoria::first() ?: Categoria::create(['nombre' => 'General', 'slug' => 'general']))->id, 'estado' => 1,
        ];
        $this->post(route('backend.news.store'), $news)->assertSessionHasNoErrors();
        $this->assertSame('published', News::where('created_by', $verified->id)->latest('id')->firstOrFail()->editorial_status);
    }

    public function test_festival_status_is_not_decided_by_the_request_for_non_publishers(): void
    {
        $province = (Provincia::first() ?: Provincia::create(['nombre' => 'Provincia de prueba']));
        $mes = Mes::first() ?: Mes::create(['nombre' => 'Enero']);
        $payload = fn (string $title) => ['title' => $title, 'body' => '<p>Detalle</p>', 'province_id' => $province->id, 'mes_id' => $mes->id, 'status' => 'published'];

        $this->actingAs($this->collaborator())->post(route('backend.festivales.store'), $payload('Festival plano '.uniqid()))->assertSessionHasNoErrors();
        $this->assertSame('draft', Festival::latest('id')->firstOrFail()->status);

        $this->actingAs($this->collaborator(true))->post(route('backend.festivales.store'), $payload('Festival verificado '.uniqid()))->assertSessionHasNoErrors();
        $this->assertSame('published', Festival::latest('id')->firstOrFail()->status);
    }

    // ---------------- Hallazgo 2: sin edicion cruzada ----------------

    public function test_verified_cannot_edit_content_of_artists_it_does_not_manage(): void
    {
        $verified = $this->collaborator(true);
        $foreign = $this->album($this->artist(), 1);

        $this->actingAs($verified)->get(route('backend.discos.edit', $foreign))->assertForbidden();
        $this->put(route('backend.discos.update', $foreign), $this->albumPayload($foreign->interprete))->assertForbidden();
    }

    public function test_a_global_update_permission_no_longer_allows_editing_other_peoples_content(): void
    {
        $user = $this->collaborator();
        $user->givePermissionTo(Permission::findOrCreate('update album', 'web'), Permission::findOrCreate('update noticia', 'web'));
        $foreign = $this->album($this->artist(), 1);

        $this->actingAs($user)->get(route('backend.discos.edit', $foreign))->assertForbidden();
    }

    public function test_creator_edits_own_album_for_any_artist_and_keeps_ownership(): void
    {
        $user = $this->collaborator();
        $artist = $this->artist();
        $own = $this->album($artist, $user->id, ['estado' => 0]);

        $this->actingAs($user)->get(route('backend.discos.edit', $own))->assertOk();
        $this->put(route('backend.discos.update', $own), $this->albumPayload($artist, ['album' => 'Renombrado']))->assertSessionHasNoErrors();

        $this->assertSame('Renombrado', $own->fresh()->album);
        $this->assertSame($user->id, (int) $own->fresh()->user_id);
    }

    public function test_editing_does_not_steal_ownership_of_an_admins_content(): void
    {
        $member = $this->collaborator();
        $artist = $this->artist();
        $this->link($artist, $member);
        $album = $this->album($artist, 1);

        $this->actingAs($member)->put(route('backend.discos.update', $album), $this->albumPayload($artist))->assertSessionHasNoErrors();

        $this->assertSame(1, (int) $album->fresh()->user_id);
    }

    // ---------------- Hallazgos 4 y 5 ----------------

    public function test_only_administrators_can_list_users(): void
    {
        Role::findOrCreate('prensa', 'web');
        $legacyPress = User::factory()->create();
        $legacyPress->assignRole('prensa');

        $this->actingAs($this->collaborator())->get(route('users.index'))->assertForbidden();
        $this->actingAs($legacyPress)->get(route('users.index'))->assertForbidden();

        Role::findOrCreate('administrador', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $this->actingAs($admin)->get(route('users.index'))->assertOk();
    }

    public function test_is_admin_only_recognizes_the_administrador_role(): void
    {
        Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertFalse($user->isAdmin());
    }

    public function test_the_prensa_role_is_not_referenced_in_application_code(): void
    {
        $hits = collect(app('files')->allFiles(app_path()))
            ->filter(fn ($file) => $file->getExtension() === 'php' && str_contains($file->getContents(), "'prensa'"))
            ->map(fn ($file) => $file->getRelativePathname());

        $this->assertSame([], $hits->values()->all());
    }
}
