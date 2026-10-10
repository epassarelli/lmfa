<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\EventProgram;
use App\Models\Interprete;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventProgramsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['responsecache.enabled' => false, 'responsecache.cache_store' => 'array']);
        Queue::fake();
        Http::fake();
    }

    private function user(string $role = 'administrador'): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'title' => 'Encuentro de prueba '.uniqid(), 'body' => '<h2>Programación</h2><p>Detalle común.</p>',
            'ticket_url' => 'https://example.test/entradas', 'editorial_status' => 'published',
            'sessions' => [
                ['title' => 'Jornada viernes '.uniqid(), 'start_at' => now()->addWeek()->setTime(21, 0)->toDateTimeString(), 'end_at' => now()->addWeek()->addDay()->setTime(3, 0)->toDateTimeString()],
                ['title' => 'Jornada sábado '.uniqid(), 'start_at' => now()->addWeek()->addDay()->setTime(21, 0)->toDateTimeString()],
                ['title' => 'Jornada domingo '.uniqid(), 'start_at' => now()->addWeek()->addDays(2)->setTime(21, 0)->toDateTimeString()],
            ],
        ], $overrides);
    }

    private function artist(): Interprete
    {
        return Interprete::create(['interprete' => 'Artista ficticio '.uniqid(), 'slug' => 'artista-ficticio-'.uniqid(), 'biografia' => '<p>Fixture</p>', 'estado' => 1, 'user_id' => auth()->id()]);
    }

    public function test_create_three_sessions_keeps_artists_separate_and_night_crossing_midnight(): void
    {
        $admin = $this->user();
        $this->actingAs($admin);
        $first = $this->artist();
        $second = $this->artist();
        $payload = $this->payload();
        $payload['sessions'][0]['interprete_ids'] = [$first->id];
        $payload['sessions'][1]['interprete_ids'] = [$second->id];
        $this->post(route('backend.event-programs.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $program = EventProgram::where('title', $payload['title'])->firstOrFail();
        $events = $program->events()->orderBy('id')->get();
        $this->assertCount(3, $events);
        $this->assertSame([$first->id], $events[0]->interpretes()->pluck('interpretes.id')->all());
        $this->assertSame([$second->id], $events[1]->interpretes()->pluck('interpretes.id')->all());
        $this->assertSame(0, $events[2]->interpretes()->count());
        $this->assertSame($program->body, $events[0]->body);
        $this->assertFalse($events[0]->start_at->isSameDay($events[0]->end_at));
        $this->assertCount(3, $events->pluck('slug')->unique());
    }

    public function test_common_correction_preserves_overrides_and_session_publication_states(): void
    {
        $this->actingAs($this->user());
        $program = app(\App\Services\EventProgramService::class)->save($this->payload());
        $events = $program->events()->orderBy('id')->get();
        app(EventService::class)->updateEvent($events[1], ['ticket_url' => 'https://example.test/otra', 'editorial_status' => 'draft']);
        $this->put(route('backend.event-programs.update', $program), array_merge($program->only(['title', 'body', 'editorial_status']), ['ticket_url' => 'https://example.test/nueva']))->assertSessionHasNoErrors();
        $this->assertSame('https://example.test/nueva', $events[0]->fresh()->ticket_url);
        $this->assertSame('https://example.test/otra', $events[1]->fresh()->ticket_url);
        $this->assertSame('draft', $events[1]->fresh()->editorial_status);
        app(EventService::class)->updateEvent($events[1], ['inherit_fields' => ['ticket_url']]);
        $this->assertSame('https://example.test/nueva', $events[1]->fresh()->ticket_url);
        $this->assertNotContains('ticket_url', $events[1]->fresh()->program_overrides);
    }

    public function test_collaborator_cannot_publish_or_modify_another_program(): void
    {
        $this->actingAs($this->user());
        $other = app(\App\Services\EventProgramService::class)->save($this->payload());
        $collaborator = $this->user('colaborador');
        $this->actingAs($collaborator);
        $this->get(route('backend.event-programs.edit', $other))->assertForbidden();
        $this->post(route('backend.events.store'), ['title' => 'Intento ajeno', 'body' => '<p>Texto</p>', 'start_at' => now()->addDay()->toDateTimeString(), 'program_id' => $other->id])->assertSessionHasErrors('program_id');
        $payload = $this->payload();
        $this->post(route('backend.event-programs.store'), $payload)->assertSessionHasNoErrors();
        $own = EventProgram::where('title', $payload['title'])->firstOrFail();
        $this->assertSame('draft', $own->editorial_status);
        $this->assertSame(3, $own->events()->where('editorial_status', 'draft')->count());
    }

    public function test_public_program_hides_drafts_future_publications_and_cancelled_sessions(): void
    {
        $this->actingAs($this->user());
        $program = app(\App\Services\EventProgramService::class)->save($this->payload());
        $events = $program->events()->orderBy('id')->get();
        $events[1]->update(['editorial_status' => 'draft']);
        $events[2]->update(['published_at' => now()->addDay()]);
        auth()->logout();
        $this->get(route('event-programs.show', $program->slug))->assertOk()->assertSee($events[0]->title)->assertDontSee($events[1]->title)->assertDontSee($events[2]->title)
            ->assertSee(route('cartelera.show', $events[0]->slug), false);
        $this->get(route('cartelera.show', $events[0]->slug))->assertOk()->assertSee(route('event-programs.show', $program->slug), false);
        $events[0]->update(['status' => 'cancelled']);
        $this->get(route('event-programs.show', $program->slug))->assertNotFound();
        $this->get(route('sitemap.events'))->assertOk()->assertDontSee(route('event-programs.show', $program->slug), false);
    }

    public function test_invalid_second_session_rolls_back_entire_program(): void
    {
        $this->actingAs($this->user());
        $payload = $this->payload();
        $payload['sessions'][1]['end_at'] = now()->subYear()->toDateTimeString();
        $count = Event::count();
        $this->post(route('backend.event-programs.store'), $payload)->assertSessionHasErrors('sessions.1.end_at');
        $this->assertFalse(EventProgram::where('title', $payload['title'])->exists());
        $this->assertSame($count, Event::count());
    }

    public function test_api_creates_program_and_partial_event_update_preserves_relations(): void
    {
        Sanctum::actingAs($this->user());
        $artist = $this->artist();
        $payload = $this->payload();
        $payload['sessions'][0]['interprete_ids'] = [$artist->id];
        $response = $this->postJson('/api/v1/event-programs', $payload)->assertCreated()->assertJsonCount(3, 'events');
        $event = Event::findOrFail($response->json('events.0.id'));
        $this->patchJson('/api/v1/events/'.$event->id, ['title' => 'Título corregido'])->assertOk();
        $this->assertSame([$artist->id], $event->interpretes()->pluck('interpretes.id')->all());
        $this->assertSame('https://example.test/entradas', $event->fresh()->ticket_url);
    }

    public function test_bandeja_publication_does_not_publish_sister_sessions(): void
    {
        $this->actingAs($this->user());
        $program = app(\App\Services\EventProgramService::class)->save($this->payload(['editorial_status' => 'draft']));
        $event = $program->events()->firstOrFail();
        $this->post(route('backend.moderation.publish', ['type' => 'event', 'id' => $event->id]))->assertRedirect();
        $this->assertSame('published', $event->fresh()->editorial_status);
        $this->assertNotNull($event->fresh()->published_at);
        $this->assertSame(2, $program->events()->where('editorial_status', 'draft')->count());
        $this->assertSame('draft', $program->fresh()->editorial_status);
    }

    public function test_forms_offer_ckeditor_and_program_loading_and_keep_legacy_event_url(): void
    {
        $this->actingAs($this->user());
        $program = app(\App\Services\EventProgramService::class)->save($this->payload());
        $event = $program->events()->firstOrFail();
        $slug = $event->slug;
        $this->get(route('backend.event-programs.create'))->assertRedirect(route('backend.events.create', ['format' => 'multiple']));
        $this->get(route('backend.events.create', ['format' => 'multiple']))->assertOk()->assertSee('Agregar jornada')->assertSee('data-ckeditor-profile="editorial-body"', false);
        $this->get(route('backend.events.edit', $event))->assertOk()->assertSee('vendor/ckeditor5/ckeditor.js')->assertDontSee('summernote')->assertSee('name="venue_id"', false);
        app(EventService::class)->updateEvent($event, ['title' => 'Título nuevo']);
        $this->assertSame($slug, $event->fresh()->slug);
    }

    public function test_program_image_is_shared_without_copying_media_for_each_session(): void
    {
        Storage::fake('public');
        $this->actingAs($this->user());
        $program = app(\App\Services\EventProgramService::class)->save($this->payload(), UploadedFile::fake()->image('fixture.jpg', 800, 600));
        $program->load('images');
        $this->assertCount(1, $program->images);
        foreach ($program->events()->with(['images', 'program.images'])->get() as $event) {
            $this->assertCount(0, $event->images);
            $image = app(\App\Services\EditorialImageResolver::class)->resolve($event);
            $this->assertTrue($image->isMedia());
            $this->assertSame($program->images->first()->id, $image->media->id);
        }
    }

    public function test_empty_inline_venue_fields_do_not_block_save_and_false_gratuity_is_inherited(): void
    {
        $this->actingAs($this->user());
        $payload = $this->payload(['is_free' => '0', 'new_venue' => ['name' => '', 'city' => '', 'address' => '', 'province_id' => '', 'latitude' => '', 'longitude' => '']]);
        $this->post(route('backend.event-programs.store'), $payload)->assertSessionHasNoErrors();
        $program = EventProgram::where('title', $payload['title'])->firstOrFail();
        $event = $program->events()->firstOrFail();
        $this->post(route('backend.events.store'), ['title' => 'Jornada agregada '.uniqid(), 'start_at' => now()->addDays(12), 'body' => $program->body,
            'program_id' => $program->id, 'is_free' => '0', 'estado' => 1, 'new_venue' => $payload['new_venue']])->assertSessionHasNoErrors();
        $added = $program->events()->latest('id')->firstOrFail();
        $this->assertNotContains('is_free', $added->program_overrides);
    }

    public function test_program_pagination_filters_public_sessions_and_queries_do_not_grow_per_row(): void
    {
        $this->actingAs($this->user());
        $payload = $this->payload();
        $payload['sessions'] = [$payload['sessions'][0]];
        $program = app(\App\Services\EventProgramService::class)->save($payload);
        $first = $program->events()->firstOrFail();
        auth()->logout();
        $this->get(route('event-programs.show', $program->slug))->assertOk();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->get(route('event-programs.show', $program->slug))->assertOk();
        $small = count(DB::getQueryLog());
        DB::disableQueryLog();
        for ($index = 1; $index <= 12; $index++) {
            Event::create(['title' => 'Jornada paginada '.$index, 'slug' => 'paginada-'.uniqid(), 'body' => '<p>Detalle</p>', 'program_id' => $program->id,
                'start_at' => $first->start_at->copy()->addDays($index), 'status' => 'active', 'editorial_status' => 'published', 'created_by' => $program->created_by]);
        }
        \Illuminate\Database\Eloquent\Model::preventLazyLoading();
        try {
            DB::enableQueryLog();
            DB::flushQueryLog();
            $response = $this->get(route('event-programs.show', $program->slug))->assertOk()->assertViewHas('sessions', fn ($sessions) => $sessions->total() === 13 && $sessions->count() === 12);
            $large = count(DB::getQueryLog());
            $this->assertLessThanOrEqual($small + 1, $large);
            $this->assertLessThan(350000, strlen($response->getContent()));
            $this->get(route('event-programs.show', ['slug' => $program->slug, 'page' => 2]))->assertOk()->assertViewHas('metaRobots', 'noindex,follow')->assertViewHas('canonicalUrl', \App\Support\CanonicalUrl::normalize(route('event-programs.show', $program->slug)));
        } finally {
            DB::disableQueryLog();
            \Illuminate\Database\Eloquent\Model::preventLazyLoading(false);
        }
    }
}
