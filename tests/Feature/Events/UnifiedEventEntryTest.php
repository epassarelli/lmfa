<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\EventProgram;
use App\Models\Interprete;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UnifiedEventEntryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['responsecache.enabled' => false, 'responsecache.cache_store' => 'array']);
        Queue::fake();
        Http::fake();
    }

    private function login(string $role = 'administrador'): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    private function payload(): array
    {
        return [
            'title' => 'Ingreso único '.uniqid(), 'body' => '<p>Detalle ficticio.</p>',
            'event_format' => 'multiple', 'estado' => 1,
            'sessions' => [
                ['title' => 'Primera jornada', 'start_at' => now()->addWeek()->toDateTimeString()],
                ['title' => 'Segunda jornada', 'start_at' => now()->addDays(8)->toDateTimeString()],
            ],
        ];
    }

    public function test_single_entry_creates_multiple_sessions_and_list_shows_one_parent(): void
    {
        $this->login();
        $artist = Interprete::create(['interprete' => 'Artista fixture '.uniqid(), 'slug' => 'artist-'.uniqid(), 'biografia' => '<p>Fixture.</p>', 'estado' => 1, 'user_id' => auth()->id()]);
        $data = $this->payload();
        $data['sessions'][1]['interprete_ids'] = [$artist->id];
        $this->post(route('backend.events.store'), $data)->assertSessionHasNoErrors();
        $program = EventProgram::where('title', $data['title'])->firstOrFail();
        $this->assertSame(2, $program->events()->count());
        $this->assertSame('published', $program->editorial_status);
        $this->get(route('backend.events.index', ['search' => $data['title']]))->assertOk()
            ->assertViewHas('events', fn ($events) => $events->total() === 1)
            ->assertSee('2 jornadas')->assertSee(route('backend.event-programs.edit', $program), false);
        $this->get(route('backend.events.index', ['search' => $artist->interprete]))->assertOk()
            ->assertViewHas('events', fn ($events) => $events->total() === 1);
        $this->get(route('backend.event-programs.edit', $program))->assertOk()->assertSee('Editar evento')->assertDontSee('conjunto');
        Event::preventLazyLoading();
        try {
            DB::enableQueryLog();
            DB::flushQueryLog();
            $this->get(route('backend.events.index', ['search' => $data['title']]))->assertOk();
            $smallCount = count(DB::getQueryLog());
            for ($day = 2; $day < 8; $day++) {
                app(EventService::class)->createEvent(['title' => 'Jornada extra '.$day, 'program_id' => $program->id, 'start_at' => now()->addDays(8 + $day)->toDateTimeString()]);
            }
            DB::flushQueryLog();
            $response = $this->get(route('backend.events.index', ['search' => $data['title'], 'sort' => 'title', 'direction' => 'asc']))->assertOk()->assertSee('8 jornadas');
            $this->assertLessThanOrEqual($smallCount + 1, count(DB::getQueryLog()));
            $this->assertLessThan(350000, strlen($response->getContent()));
        } finally {
            Event::preventLazyLoading(false);
            DB::disableQueryLog();
        }
    }

    public function test_single_date_and_overnight_event_use_same_entry_without_program(): void
    {
        $this->login();
        $data = ['event_format' => 'single', 'title' => 'Una noche '.uniqid(), 'body' => '<p>Detalle.</p>', 'start_at' => now()->addWeek()->setTime(22, 0)->toDateTimeString(), 'end_at' => now()->addDays(8)->setTime(3, 0)->toDateTimeString(), 'estado' => 0];
        $this->post(route('backend.events.store'), $data)->assertSessionHasNoErrors()->assertRedirect(route('backend.events.index'));
        $event = Event::where('title', $data['title'])->firstOrFail();
        $this->assertNull($event->program_id);
        $this->assertSame('draft', $event->editorial_status);
    }

    public function test_invalid_multiple_mode_keeps_input_and_rolls_back(): void
    {
        $this->login();
        $data = $this->payload();
        $data['sessions'][1]['end_at'] = now()->toDateTimeString();
        $this->from(route('backend.events.create'))->post(route('backend.events.store'), $data)
            ->assertSessionHasErrors('sessions.1.end_at')->assertSessionHasInput('event_format', 'multiple');
        $this->assertFalse(EventProgram::where('title', $data['title'])->exists());
        $data['sessions'] = [$data['sessions'][0]];
        $this->post(route('backend.events.store'), $data)->assertSessionHasErrors('sessions');
    }

    public function test_existing_event_can_gain_sessions_without_replacing_its_identity(): void
    {
        $this->login();
        $artist = Interprete::create(['interprete' => 'Fixture '.uniqid(), 'slug' => 'fixture-'.uniqid(), 'biografia' => '<p>Fixture</p>', 'estado' => 1, 'user_id' => auth()->id()]);
        $event = app(EventService::class)->createEvent(['title' => 'Evento anterior '.uniqid(), 'body' => '<p>Detalle original</p>', 'start_at' => now()->addWeek()->toDateTimeString(), 'interprete_ids' => [$artist->id], 'editorial_status' => 'draft']);
        $slug = $event->slug;
        $this->post(route('backend.events.sessions', $event))->assertRedirect();
        $event->refresh();
        $this->assertNotNull($event->program_id);
        $this->assertSame($slug, $event->slug);
        $this->assertSame('draft', $event->editorial_status);
        $this->assertSame([$artist->id], $event->interpretes()->pluck('interpretes.id')->all());
        $this->assertSame(1, $event->program->events()->count());
        $programId = $event->program_id;
        $this->post(route('backend.events.sessions', $event))->assertRedirect();
        $this->assertSame($programId, $event->fresh()->program_id);
        $this->get(route('backend.events.create', ['program_id' => $programId]))->assertOk()->assertViewHas('event', fn ($event) => $event->program_id === $programId);
    }

    public function test_collaborator_cannot_convert_someone_else_event_or_publish_multiple_days(): void
    {
        $this->login();
        $event = app(EventService::class)->createEvent(['title' => 'Ajeno '.uniqid(), 'body' => '<p>Detalle</p>', 'start_at' => now()->addWeek()->toDateTimeString()]);
        $this->login('colaborador');
        $this->post(route('backend.events.sessions', $event))->assertForbidden();
        $this->assertNull($event->fresh()->program_id);
        $data = $this->payload();
        $this->post(route('backend.events.store'), $data)->assertSessionHasNoErrors();
        $program = EventProgram::where('title', $data['title'])->firstOrFail();
        $this->assertSame('draft', $program->editorial_status);
        $this->assertSame(0, $program->events()->where('editorial_status', 'published')->count());
    }

    public function test_sidebar_and_legacy_entry_point_lead_to_unified_form(): void
    {
        $this->login();
        $this->get(route('backend.events.create'))->assertOk()->assertSee('Una fecha')->assertSee('Varias jornadas')->assertSee('Agregar jornada')->assertDontSee('Conjuntos / Jornadas');
        $this->get(route('backend.event-programs.index'))->assertRedirect(route('backend.events.index'));
        $this->get(route('backend.event-programs.create'))->assertRedirect(route('backend.events.create', ['format' => 'multiple']));
    }

    public function test_administrator_with_collaborator_role_can_list_other_authors_events(): void
    {
        $this->login();
        $event = app(EventService::class)->createEvent(['title' => 'Evento de otro autor '.uniqid(), 'body' => '<p>Fixture</p>', 'start_at' => now()->addWeek()->toDateTimeString()]);
        $admin = $this->login();
        foreach (['colaborador', 'prensa'] as $role) {
            Role::findOrCreate($role, 'web');
            $admin->assignRole($role);
            $this->get(route('backend.events.index', ['search' => $event->title]))->assertOk()
                ->assertViewHas('events', fn ($events) => $events->total() === 1);
        }
        $this->login('colaborador');
        $this->get(route('backend.events.index', ['search' => $event->title]))->assertOk()
            ->assertViewHas('events', fn ($events) => $events->total() === 0);
    }
}
