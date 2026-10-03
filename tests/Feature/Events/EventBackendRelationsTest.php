<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\Festival;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\Mes;
use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventBackendRelationsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_form_exposes_relation_selects_end_at_and_excerpt(): void
    {
        $admin = $this->userWithRole('administrador');
        $festival = $this->makeFestival();
        $event = $this->makeEvent($admin);
        $event->festivales()->attach($festival->id);

        $this->actingAs($admin)
            ->get(route('backend.events.edit', $event))
            ->assertOk()
            ->assertSee('name="festival_ids[]"', false)
            ->assertSee('name="knowledge_article_ids[]"', false)
            ->assertSee('name="penia_profile_ids[]"', false)
            ->assertSee('name="end_at"', false)
            ->assertSee('name="excerpt"', false)
            ->assertSee('class="form-control select2" multiple', false)
            ->assertSee('<option value="'.$festival->id.'" selected', false);
    }

    public function test_store_syncs_existing_pivots_and_saves_end_at_and_excerpt(): void
    {
        $admin = $this->userWithRole('administrador');
        $festival = $this->makeFestival();
        $article = $this->makeArticle();
        $penia = PeniaProfile::factory()->create(['slug' => 'pena-backend-'.uniqid()]);

        $this->actingAs($admin)
            ->post(route('backend.events.store'), $this->payload([
                'title' => 'Evento con relaciones '.uniqid(),
                'end_at' => now()->addWeek()->addHours(4)->format('Y-m-d\TH:i'),
                'excerpt' => 'Resumen breve del evento.',
                'festival_ids' => ['', (string) $festival->id],
                'knowledge_article_ids' => ['', (string) $article->id],
                'penia_profile_ids' => ['', (string) $penia->id],
            ]))
            ->assertRedirect(route('backend.events.index'))
            ->assertSessionHasNoErrors();

        $event = Event::latest('id')->firstOrFail();

        $this->assertSame('Resumen breve del evento.', $event->excerpt);
        $this->assertNotNull($event->end_at);
        $this->assertSame([$festival->id], $event->festivales()->pluck('festivales.id')->all());
        $this->assertSame([$article->id], $event->knowledgeArticles()->pluck('knowledge_articles.id')->all());
        $this->assertSame([$penia->id], $event->peniaProfiles()->pluck('penia_profiles.id')->all());
    }

    public function test_update_can_detach_all_relations_with_the_empty_hidden_value(): void
    {
        $admin = $this->userWithRole('administrador');
        $event = $this->makeEvent($admin);
        $event->festivales()->attach($this->makeFestival()->id);
        $event->knowledgeArticles()->attach($this->makeArticle()->id);

        $this->actingAs($admin)
            ->put(route('backend.events.update', $event), $this->payload([
                'title' => $event->title,
                'festival_ids' => [''],
                'knowledge_article_ids' => [''],
                'penia_profile_ids' => [''],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $event->festivales()->count());
        $this->assertSame(0, $event->knowledgeArticles()->count());
    }

    public function test_payload_without_relation_keys_keeps_existing_pivots(): void
    {
        $admin = $this->userWithRole('administrador');
        $event = $this->makeEvent($admin);
        $festival = $this->makeFestival();
        $event->festivales()->attach($festival->id);

        $this->actingAs($admin)
            ->put(route('backend.events.update', $event), $this->payload(['title' => $event->title]))
            ->assertSessionHasNoErrors();

        $this->assertSame([$festival->id], $event->festivales()->pluck('festivales.id')->all());
    }

    public function test_invalid_relation_ids_and_end_before_start_are_rejected(): void
    {
        $admin = $this->userWithRole('administrador');

        $this->actingAs($admin)
            ->post(route('backend.events.store'), $this->payload([
                'title' => 'Evento inválido '.uniqid(),
                'end_at' => now()->subYear()->format('Y-m-d\TH:i'),
                'festival_ids' => ['999999999'],
            ]))
            ->assertSessionHasErrors(['end_at', 'festival_ids.0']);
    }

    public function test_collaborator_cannot_relate_events_created_by_someone_else(): void
    {
        $owner = $this->userWithRole('administrador');
        $collaborator = $this->userWithRole('colaborador');
        $event = $this->makeEvent($owner);
        $festival = $this->makeFestival();

        $this->actingAs($collaborator)
            ->put(route('backend.events.update', $event), $this->payload([
                'title' => $event->title,
                'festival_ids' => [(string) $festival->id],
            ]))
            ->assertForbidden();

        $this->actingAs($collaborator)->get(route('backend.events.edit', $event))->assertForbidden();
        $this->assertSame(0, $event->festivales()->count());
    }

    public function test_collaborator_can_relate_their_own_events(): void
    {
        $collaborator = $this->userWithRole('colaborador');
        $event = $this->makeEvent($collaborator);
        $festival = $this->makeFestival();

        $this->actingAs($collaborator)
            ->put(route('backend.events.update', $event), $this->payload([
                'title' => $event->title,
                'estado' => 0,
                'festival_ids' => ['', (string) $festival->id],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame([$festival->id], $event->festivales()->pluck('festivales.id')->all());
    }

    // ------------------------------------------------------------------

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'body' => '<p>Detalle del evento.</p>',
            'start_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'estado' => 1,
            'city' => 'Cosquín',
        ], $overrides);
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function makeEvent(User $owner): Event
    {
        return Event::create([
            'title' => 'Evento backend '.uniqid(),
            'slug' => 'evento-backend-'.uniqid(),
            'body' => '<p>Contenido.</p>',
            'start_at' => now()->addWeek(),
            'editorial_status' => 'published',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);
    }

    private function makeFestival(): Festival
    {
        return Festival::create([
            'title' => 'Festival backend '.uniqid(),
            'slug' => 'festival-backend-'.uniqid(),
            'body' => '<p>Festival.</p>',
            'province_id' => Provincia::create(['nombre' => 'Provincia Backend '.Str::upper(Str::random(8))])->id,
            'mes_id' => Mes::firstOrCreate(['nombre' => 'Febrero'])->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'visitas' => 0,
        ]);
    }

    private function makeArticle(): KnowledgeArticle
    {
        return KnowledgeArticle::factory()->create([
            'knowledge_category_id' => KnowledgeCategory::factory()->create(['slug' => 'categoria-backend-'.uniqid()])->id,
            'slug' => 'articulo-backend-'.uniqid(),
        ]);
    }
}
