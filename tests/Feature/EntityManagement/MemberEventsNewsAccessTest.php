<?php

namespace Tests\Feature\EntityManagement;

use App\Models\Categoria;
use App\Models\Event;
use App\Models\EventProgram;
use App\Models\Interprete;
use App\Models\News;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemberEventsNewsAccessTest extends TestCase
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
    }

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
        return Interprete::create(['interprete' => 'Artista '.$slug.' '.uniqid(), 'slug' => $slug.'-'.uniqid(), 'biografia' => 'Bio de prueba.', 'estado' => 1, 'user_id' => 1]);
    }

    private function link(Interprete $artist, User $user, string $role = 'editor'): void
    {
        $org = Organization::create(['type' => 'artista', 'name' => 'Org '.uniqid(), 'status' => 'active']);
        OrganizationEntity::create(['organization_id' => $org->id, 'entity_type' => $artist->getMorphClass(), 'entity_id' => $artist->id, 'is_primary' => true]);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => $role, 'status' => 'active']);
    }

    private function event(array $artists, ?User $creator = null, array $extra = []): Event
    {
        $title = 'Evento '.uniqid();
        $event = Event::create(array_merge([
            'title' => $title, 'slug' => Str::slug($title), 'body' => '<p>Detalle</p>', 'start_at' => now()->addWeek(),
            'editorial_status' => 'published', 'status' => 'active', 'modality' => 'presencial', 'created_by' => ($creator ?? $this->user('administrador'))->id,
        ], $extra));
        $event->interpretes()->sync(collect($artists)->pluck('id')->all());

        return $event;
    }

    private function eventPayload(array $artists, array $extra = []): array
    {
        return array_merge([
            'event_format' => 'single', 'title' => 'Nuevo evento '.uniqid(), 'body' => '<p>Detalle</p>', 'start_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'interprete_ids' => collect($artists)->pluck('id')->all(), 'estado' => 1,
        ], $extra);
    }

    private function news(array $artists, ?User $creator = null): News
    {
        $title = 'Noticia '.uniqid();
        $news = News::create([
            'title' => $title, 'slug' => Str::slug($title), 'body' => '<p>Cuerpo</p>', 'editorial_status' => 'published', 'estado' => 1,
            'created_by' => ($creator ?? $this->user('administrador'))->id, 'user_id' => 1,
            'interprete_id' => $artists[0]->id, 'categoria_id' => $this->category()->id, 'published_at' => now(),
        ]);
        $news->interpretes()->sync(collect($artists)->slice(1)->pluck('id')->all());

        return $news;
    }

    private function category(): Categoria
    {
        return Categoria::first() ?: Categoria::create(['nombre' => 'General', 'slug' => 'general']);
    }

    private function newsPayload(Interprete $principal, array $secondary = [], array $extra = []): array
    {
        $title = 'Noticia nueva '.uniqid();

        return array_merge([
            'titulo' => $title, 'slug' => Str::slug($title), 'noticia' => '<p>Cuerpo de la noticia</p>', 'foto' => UploadedFile::fake()->image('n.jpg'),
            'interprete_principal_id' => $principal->id, 'interprete_secundarios' => collect($secondary)->pluck('id')->all(),
            'categoria_id' => $this->category()->id, 'estado' => 1,
        ], $extra);
    }

    // ---------------- Eventos ----------------

    public function test_editor_creates_event_with_own_artist_and_guest_and_it_goes_to_review(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $guest = $this->artist('invitado');
        $this->link($mine, $user);

        $data = $this->eventPayload([$mine, $guest]);
        $this->actingAs($user)->post(route('backend.events.store'), $data)->assertSessionHasNoErrors()->assertRedirect();

        $event = Event::where('title', $data['title'])->firstOrFail();
        $this->assertSame('draft', $event->editorial_status);
        $this->assertSame($user->id, (int) $event->created_by);
        $this->assertEqualsCanonicalizing([$mine->id, $guest->id], $event->linkedInterpreteIds());
    }

    public function test_event_without_any_own_artist_is_rejected_for_a_pure_editor(): void
    {
        $user = $this->user();
        $this->link($this->artist('propio'), $user);
        $foreign = $this->artist('ajeno');

        $this->actingAs($user)->post(route('backend.events.store'), $this->eventPayload([$foreign]))->assertSessionHasErrors('interprete_ids');
        $this->post(route('backend.events.store'), $this->eventPayload([]))->assertSessionHasErrors('interprete_ids');
    }

    public function test_editor_edits_only_events_where_all_artists_are_theirs_or_that_they_created(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $foreign = $this->artist('ajeno');
        $this->link($mine, $user);

        $onlyMine = $this->event([$mine]);
        $shared = $this->event([$mine, $foreign]);
        $createdByMeShared = $this->event([$mine, $foreign], $user);

        $this->actingAs($user)->get(route('backend.events.edit', $onlyMine))->assertOk();
        $this->put(route('backend.events.update', $onlyMine), $this->eventPayload([$mine], ['title' => 'Editado por miembro']))->assertSessionHasNoErrors();
        $this->assertSame('Editado por miembro', $onlyMine->fresh()->title);
        $this->assertSame('draft', $onlyMine->fresh()->editorial_status);

        $this->get(route('backend.events.edit', $shared))->assertForbidden();
        $this->put(route('backend.events.update', $shared), $this->eventPayload([$mine, $foreign]))->assertForbidden();

        $this->get(route('backend.events.edit', $createdByMeShared))->assertOk();
        $this->put(route('backend.events.update', $onlyMine), $this->eventPayload([$foreign]))->assertSessionHasErrors('interprete_ids');
    }

    public function test_event_listing_only_shows_own_or_editable_events(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $foreign = $this->artist('ajeno');
        $this->link($mine, $user);
        $visible = $this->event([$mine], null, ['title' => 'Visible para el miembro']);
        $hidden = $this->event([$foreign], null, ['title' => 'Oculto para el miembro']);

        $this->actingAs($user)->get(route('backend.events.index'))->assertOk()->assertSee($visible->title)->assertDontSee($hidden->title);
    }

    public function test_forged_published_status_never_publishes_for_non_publishers(): void
    {
        $collaborator = $this->user('colaborador');
        $data = $this->eventPayload([], ['estado' => 1]);

        $this->actingAs($collaborator)->post(route('backend.events.store'), $data)->assertSessionHasNoErrors();

        $this->assertSame('draft', Event::where('title', $data['title'])->firstOrFail()->editorial_status);
    }

    public function test_administrator_still_publishes_events_directly(): void
    {
        $admin = $this->user('administrador');
        $data = $this->eventPayload([], ['estado' => 1]);

        $this->actingAs($admin)->post(route('backend.events.store'), $data)->assertSessionHasNoErrors();

        $this->assertSame('published', Event::where('title', $data['title'])->firstOrFail()->editorial_status);
    }

    public function test_program_sessions_follow_the_same_rule(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $foreign = $this->artist('ajeno');
        $this->link($mine, $user);
        $admin = $this->user('administrador');

        $own = EventProgram::create(['title' => 'Programa propio', 'slug' => 'programa-propio-'.uniqid(), 'body' => '<p>Detalle</p>', 'editorial_status' => 'draft', 'created_by' => $admin->id]);
        $this->event([$mine], null, ['program_id' => $own->id]);
        $other = EventProgram::create(['title' => 'Programa mixto', 'slug' => 'programa-mixto-'.uniqid(), 'body' => '<p>Detalle</p>', 'editorial_status' => 'draft', 'created_by' => $admin->id]);
        $this->event([$mine], null, ['program_id' => $other->id]);
        $this->event([$foreign], null, ['program_id' => $other->id]);

        $this->actingAs($user)->get(route('backend.event-programs.edit', $own))->assertOk();
        $this->get(route('backend.event-programs.edit', $other))->assertForbidden();
    }

    // ---------------- Noticias ----------------

    public function test_editor_creates_news_with_own_principal_and_guest_secondary_pending_review(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $guest = $this->artist('invitado');
        $this->link($mine, $user);

        $data = $this->newsPayload($mine, [$guest]);
        $this->actingAs($user)->post(route('backend.news.store'), $data)->assertSessionHasNoErrors()->assertRedirect();

        $news = News::where('created_by', $user->id)->latest('id')->firstOrFail();
        $this->assertSame('draft', $news->editorial_status);
        $this->assertSame(0, (int) $news->estado);
    }

    public function test_news_without_any_own_artist_is_rejected(): void
    {
        $user = $this->user();
        $this->link($this->artist('propio'), $user);

        $this->actingAs($user)->post(route('backend.news.store'), $this->newsPayload($this->artist('ajeno')))->assertSessionHasErrors('interprete_principal_id');
    }

    public function test_editor_edits_news_only_when_all_linked_artists_are_theirs(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $foreign = $this->artist('ajeno');
        $this->link($mine, $user);
        $onlyMine = $this->news([$mine]);
        $mixed = $this->news([$mine, $foreign]);

        $this->actingAs($user)->get(route('backend.news.edit', $onlyMine))->assertOk();
        $this->get(route('backend.news.edit', $mixed))->assertForbidden();
    }

    public function test_news_listing_is_scoped_for_a_pure_editor(): void
    {
        $user = $this->user();
        $mine = $this->artist('propio');
        $foreign = $this->artist('ajeno');
        $this->link($mine, $user);
        $visible = $this->news([$mine]);
        $hidden = $this->news([$foreign]);

        $this->actingAs($user)->get(route('backend.news.index'))->assertOk()->assertSee($visible->title)->assertDontSee($hidden->title);
    }

    public function test_user_without_links_or_permissions_cannot_use_events_or_news(): void
    {
        $this->actingAs($this->user())->post(route('backend.events.store'), $this->eventPayload([]))->assertForbidden();
        $this->post(route('backend.news.store'), $this->newsPayload($this->artist('ajeno')))->assertForbidden();
        $this->get(route('backend.news.index'))->assertForbidden();
    }
}
