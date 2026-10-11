<?php

namespace Tests\Feature\Events;

use App\Models\Contribution;
use App\Models\Event;
use App\Models\EventProgram;
use App\Models\Interprete;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventEditorialContributionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['responsecache.enabled' => false, 'responsecache.cache_store' => 'array']);
        Http::fake();
        Queue::fake();
    }

    private function admin(): User
    {
        Role::findOrCreate('administrador', 'web');
        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    public function test_contribution_can_propose_multiple_days_and_approval_saves_all_sessions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $artist = Interprete::create(['interprete' => 'Artista propuesta '.uniqid(), 'slug' => 'artista-propuesta-'.uniqid(), 'biografia' => '<p>Fixture ficticia.</p>', 'estado' => 1, 'user_id' => $user->id]);
        $payload = ['title' => 'Edición propuesta '.uniqid(), 'body' => '<h1>Detalle</h1><p>Propuesta de prueba.</p>',
            'sessions' => [
                ['title' => 'Primera noche '.uniqid(), 'start_at' => now()->addWeek()->toDateTimeString(), 'interprete_ids' => ['', $artist->id]],
                ['title' => 'Segunda noche '.uniqid(), 'start_at' => now()->addWeek()->addDay()->toDateTimeString()],
            ]];
        $this->get(route('backend.contributions.create', ['type' => 'event']))->assertOk()->assertSee('Tiene varias jornadas')->assertSee('data-ckeditor-profile="editorial-body"', false);
        $count = Event::count();
        $this->post(route('backend.contributions.store'), ['type' => 'event', 'payload' => $payload])->assertSessionHasNoErrors();
        $this->assertSame($count, Event::count());
        $contribution = Contribution::where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->assertSame('pending', $contribution->status);
        $this->actingAs($this->admin());
        $this->get(route('backend.contributions.show', $contribution))->assertOk()->assertSee('Primera noche');
        $this->post(route('backend.contributions.approve', $contribution))->assertSessionHas('success');
        $program = EventProgram::where('title', $payload['title'])->firstOrFail();
        $this->assertSame($user->id, $program->created_by);
        $this->assertSame(2, $program->events()->count());
        $this->assertSame(2, $program->events()->where('editorial_status', 'published')->count());
        $this->assertStringNotContainsString('<h1>', $program->body);
        $this->assertSame([$artist->id], $program->events()->orderBy('id')->first()->interpretes()->pluck('interpretes.id')->all());
        $this->assertSame('approved', $contribution->fresh()->status);
    }

    public function test_event_contribution_approval_uses_service_to_sync_artists_and_preserve_slug(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $artist = Interprete::create(['interprete' => 'Artista edición '.uniqid(), 'slug' => 'artista-edicion-'.uniqid(), 'biografia' => '<p>Fixture ficticia.</p>', 'estado' => 1, 'user_id' => $user->id]);
        $event = Event::create(['title' => 'Evento corregible', 'slug' => 'evento-corregible-'.uniqid(), 'body' => '<p>Original</p>', 'start_at' => now()->addWeek(), 'created_by' => $user->id]);
        $slug = $event->slug;
        $this->post(route('backend.contributions.store'), ['type' => 'event', 'id' => $event->id, 'payload' => ['title' => 'Evento corregido', 'body' => '<h1>Nuevo</h1>', 'start_at' => $event->start_at->toDateTimeString(), 'interprete_ids' => [$artist->id]]])->assertSessionHasNoErrors();
        $contribution = Contribution::where('contributable_id', $event->id)->latest('id')->firstOrFail();
        $this->actingAs($this->admin());
        $this->post(route('backend.contributions.approve', $contribution))->assertSessionHas('success');
        $this->assertSame($slug, $event->fresh()->slug);
        $this->assertSame('published', $event->fresh()->editorial_status);
        $this->assertSame('<h2>Nuevo</h2>', $event->fresh()->body);
        $this->assertSame([$artist->id], $event->interpretes()->pluck('interpretes.id')->all());
    }

    public function test_invalid_session_is_rejected_before_entering_editorial_inbox(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('backend.contributions.store'), ['type' => 'event', 'payload' => ['title' => 'Inválido', 'body' => '<p>Texto</p>', 'sessions' => [['title' => 'Jornada', 'start_at' => now()->addDay()->toDateTimeString(), 'end_at' => now()->subDay()->toDateTimeString()]]]])->assertSessionHasErrors();
    }
}
