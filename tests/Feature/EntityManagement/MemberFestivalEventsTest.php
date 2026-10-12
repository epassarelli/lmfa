<?php

namespace Tests\Feature\EntityManagement;

use App\Models\Event;
use App\Models\EventProgram;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemberFestivalEventsTest extends TestCase
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

    private function artist(): Interprete
    {
        return Interprete::create(['interprete' => 'Artista '.uniqid(), 'slug' => 'a-'.uniqid(), 'biografia' => 'Bio de prueba.', 'estado' => 1, 'user_id' => 1]);
    }

    private function festival(): Festival
    {
        $mes = Mes::first() ?: Mes::create(['nombre' => 'Enero']);

        return Festival::forceCreate(['title' => 'Festival '.uniqid(), 'slug' => 'festival-'.uniqid(), 'mes_id' => $mes->id, 'user_id' => 1, 'status' => 'published']);
    }

    private function link(Model $entity, User $user, string $role = 'editor', string $status = 'active'): void
    {
        $org = Organization::create(['type' => 'festival', 'name' => 'Org '.uniqid(), 'status' => 'active']);
        OrganizationEntity::create(['organization_id' => $org->id, 'entity_type' => $entity->getMorphClass(), 'entity_id' => $entity->getKey(), 'is_primary' => true]);
        OrganizationMember::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => $role, 'status' => $status]);
    }

    private function event(array $artists, array $festivals, ?User $creator = null, array $extra = []): Event
    {
        $title = 'Evento '.uniqid();
        $event = Event::create(array_merge([
            'title' => $title, 'slug' => Str::slug($title), 'body' => '<p>Detalle</p>', 'start_at' => now()->addWeek(),
            'editorial_status' => 'published', 'status' => 'active', 'modality' => 'presencial', 'created_by' => ($creator ?? $this->user('administrador'))->id,
        ], $extra));
        $event->interpretes()->sync(collect($artists)->pluck('id')->all());
        $event->festivales()->sync(collect($festivals)->pluck('id')->all());

        return $event;
    }

    private function payload(array $artists, array $festivals, array $extra = []): array
    {
        return array_merge([
            'event_format' => 'single', 'title' => 'Nuevo evento '.uniqid(), 'body' => '<p>Detalle</p>', 'start_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'interprete_ids' => collect($artists)->pluck('id')->all(), 'festival_ids' => collect($festivals)->pluck('id')->all(), 'estado' => 1,
        ], $extra);
    }

    public function test_festival_editor_creates_event_with_unrelated_artists_and_it_goes_to_review(): void
    {
        $user = $this->user();
        $festival = $this->festival();
        $this->link($festival, $user);
        $lineup = [$this->artist(), $this->artist()];

        $data = $this->payload($lineup, [$festival]);
        $this->actingAs($user)->post(route('backend.events.store'), $data)->assertSessionHasNoErrors()->assertRedirect();

        $event = Event::where('title', $data['title'])->firstOrFail();
        $this->assertSame('draft', $event->editorial_status);
        $this->assertSame($user->id, (int) $event->created_by);
        $this->assertSame([$festival->id], $event->linkedFestivalIds());
    }

    public function test_event_without_own_artist_or_festival_is_rejected(): void
    {
        $user = $this->user();
        $this->link($this->festival(), $user);

        $this->actingAs($user)->post(route('backend.events.store'), $this->payload([$this->artist()], [$this->festival()]))->assertSessionHasErrors('interprete_ids');
        $this->post(route('backend.events.store'), $this->payload([], []))->assertSessionHasErrors('interprete_ids');
    }

    public function test_festival_manager_edits_events_of_their_festival_even_with_foreign_artists(): void
    {
        $user = $this->user();
        $festival = $this->festival();
        $this->link($festival, $user);
        $lineup = $this->event([$this->artist(), $this->artist()], [$festival]);

        $this->actingAs($user)->get(route('backend.events.edit', $lineup))->assertOk();
        $this->put(route('backend.events.update', $lineup), $this->payload([$this->artist()], [$festival], ['title' => 'Grilla editada']))->assertSessionHasNoErrors();

        $this->assertSame('Grilla editada', $lineup->fresh()->title);
        $this->assertSame('draft', $lineup->fresh()->editorial_status);
    }

    public function test_events_linked_to_a_foreign_festival_stay_with_that_festival(): void
    {
        $user = $this->user();
        $mine = $this->festival();
        $foreign = $this->festival();
        $this->link($mine, $user);

        $mixed = $this->event([], [$mine, $foreign]);
        $this->actingAs($user)->get(route('backend.events.edit', $mixed))->assertForbidden();
        $this->put(route('backend.events.update', $mixed), $this->payload([], [$mine, $foreign]))->assertForbidden();
    }

    public function test_an_artist_manager_cannot_edit_an_event_that_belongs_to_a_foreign_festival(): void
    {
        $user = $this->user();
        $artist = $this->artist();
        $this->link($artist, $user);

        $this->actingAs($user)->get(route('backend.events.edit', $this->event([$artist], [$this->festival()])))->assertForbidden();
        $this->get(route('backend.events.edit', $this->event([$artist], [])))->assertOk();
    }

    public function test_the_creator_keeps_access_to_their_event(): void
    {
        $user = $this->user();
        $festival = $this->festival();
        $this->link($festival, $user);
        $own = $this->event([$this->artist()], [$festival, $this->festival()], $user);

        $this->actingAs($user)->get(route('backend.events.edit', $own))->assertOk();
    }

    public function test_read_only_or_inactive_festival_links_do_not_grant_access(): void
    {
        $festival = $this->festival();
        $event = $this->event([], [$festival]);

        foreach ([['member', 'active'], ['editor', 'inactive'], ['editor', 'pending']] as [$role, $status]) {
            $user = $this->user();
            $this->link($festival, $user, $role, $status);

            $this->actingAs($user)->get(route('backend.events.edit', $event))->assertForbidden();
            $this->post(route('backend.events.store'), $this->payload([], [$festival]))->assertForbidden();
        }
    }

    public function test_event_listing_includes_festival_events_and_hides_the_rest(): void
    {
        $user = $this->user();
        $festival = $this->festival();
        $this->link($festival, $user);
        $visible = $this->event([$this->artist()], [$festival], null, ['title' => 'Visible por festival']);
        $hidden = $this->event([$this->artist()], [$this->festival()], null, ['title' => 'Oculto de otro festival']);

        $this->actingAs($user)->get(route('backend.events.index'))->assertOk()->assertSee($visible->title)->assertDontSee($hidden->title);
    }

    public function test_program_sessions_of_a_managed_festival_are_editable(): void
    {
        $user = $this->user();
        $festival = $this->festival();
        $this->link($festival, $user);
        $admin = $this->user('administrador');

        $program = EventProgram::create(['title' => 'Programa del festival', 'slug' => 'programa-'.uniqid(), 'body' => '<p>Detalle</p>', 'editorial_status' => 'draft', 'created_by' => $admin->id]);
        $this->event([$this->artist()], [$festival], null, ['program_id' => $program->id]);
        $this->event([$this->artist()], [$festival], null, ['program_id' => $program->id]);

        $foreignProgram = EventProgram::create(['title' => 'Programa ajeno', 'slug' => 'programa-'.uniqid(), 'body' => '<p>Detalle</p>', 'editorial_status' => 'draft', 'created_by' => $admin->id]);
        $this->event([], [$this->festival()], null, ['program_id' => $foreignProgram->id]);

        $this->actingAs($user)->get(route('backend.event-programs.edit', $program))->assertOk();
        $this->get(route('backend.event-programs.edit', $foreignProgram))->assertForbidden();
    }

    public function test_user_without_links_and_administrator_are_unchanged(): void
    {
        $event = $this->event([], [$this->festival()]);

        $this->actingAs($this->user())->post(route('backend.events.store'), $this->payload([], []))->assertForbidden();
        $this->actingAs($this->user('administrador'))->get(route('backend.events.edit', $event))->assertOk();
    }
}
