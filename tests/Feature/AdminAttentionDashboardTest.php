<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\EntityClaimRequest;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\User;
use App\Services\AdminAttentionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAttentionDashboardTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }

    private function message(array $extra = []): ContactMessage
    {
        return ContactMessage::create(array_merge([
            'nombre' => 'Persona', 'email' => 'p@example.test', 'asunto' => 'Consulta General',
            'mensaje' => 'Mensaje de prueba', 'fecha_envio' => now(), 'status' => 'new',
        ], $extra));
    }

    public function test_urgency_color_depends_on_age_of_the_oldest_pending(): void
    {
        $service = app(AdminAttentionService::class);

        $this->assertSame('success', $service->urgency(0, null));
        $this->assertSame('info', $service->urgency(2, now()->subDay()->timestamp));
        $this->assertSame('warning', $service->urgency(2, now()->subDays(AdminAttentionService::WARNING_DAYS)->timestamp));
        $this->assertSame('danger', $service->urgency(2, now()->subDays(AdminAttentionService::DANGER_DAYS)->timestamp));
    }

    public function test_administrators_see_the_attention_panel_and_collaborators_do_not(): void
    {
        $this->message();

        $this->actingAs($this->user('administrador'))->get(route('dashboard'))->assertOk()
            ->assertSee('Requiere tu atención')
            ->assertSee('data-attention="contacts"', false)
            ->assertSee('data-attention="organizations"', false)
            ->assertSee('Últimos 7 días');

        $this->actingAs($this->user('colaborador'))->get(route('dashboard'))->assertOk()
            ->assertDontSee('Requiere tu atención')
            ->assertDontSee('data-attention=', false);
    }

    public function test_the_card_count_and_color_follow_the_pending_messages(): void
    {
        ContactMessage::query()->delete();
        $this->message(['fecha_envio' => now()->subDays(8)]);
        $this->message(['status' => 'read']);
        $this->message(['status' => 'spam']);

        $card = collect(app(AdminAttentionService::class)->cards())->firstWhere('key', 'contacts');

        $this->assertSame(1, $card['count']);
        $this->assertSame('danger', $card['color']);
    }

    public function test_the_cache_is_invalidated_when_a_pending_item_changes(): void
    {
        ContactMessage::query()->delete();
        $service = app(AdminAttentionService::class);
        $message = $this->message();

        $this->assertSame(1, $service->badge('contacts')['count']);

        $message->update(['status' => 'read']);

        $this->assertSame(0, $service->badge('contacts')['count']);
    }

    public function test_the_sidebar_shows_pending_badges_to_administrators(): void
    {
        EntityClaimRequest::query()->delete();
        $admin = $this->user('administrador');
        EntityClaimRequest::create([
            'user_id' => $admin->id, 'kind' => EntityClaimRequest::KIND_CREATE,
            'entity_type' => (new Interprete)->getMorphClass(), 'proposed_name' => 'Ficha de prueba', 'relationship' => 'artist', 'message' => 'Mensaje de prueba para la solicitud.', 'status' => EntityClaimRequest::PENDING,
        ]);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('badge-info', false);
        $this->assertSame(1, app(AdminAttentionService::class)->badge('claims')['count']);
    }

    public function test_moderation_center_lists_and_publishes_draft_festivals(): void
    {
        $festival = Festival::create(['title' => 'Festival en borrador de prueba', 'slug' => 'festival-borrador-prueba-att', 'status' => 'draft', 'mes_id' => (Mes::first() ?: Mes::create(['nombre' => 'Enero']))->id]);
        $admin = $this->user('administrador');

        $this->actingAs($admin)->get(route('backend.moderation.index'))->assertOk()->assertSee('Festival en borrador de prueba');

        $this->actingAs($admin)->post(route('backend.moderation.publish', ['type' => 'festival', 'id' => $festival->id]))->assertRedirect();

        $this->assertSame('published', $festival->fresh()->status);
        $this->assertNotNull($festival->fresh()->published_at);
    }
}
