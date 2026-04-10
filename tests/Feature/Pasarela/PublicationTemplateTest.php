<?php

namespace Tests\Feature\Pasarela;

use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\PublicationRequest;
use App\Models\PublicationTarget;
use App\Models\PublicationTemplate;
use App\Models\User;
use App\Services\Publication\TemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * PC-08-HU-01: Tests del sistema de templates por canal.
 *
 * Cubre:
 *  - TemplateService usa template exacto (content_type + provider + variant)
 *  - TemplateService hace fallback a provider sin variant
 *  - TemplateService hace fallback a copy por defecto si no hay template
 *  - Token replacement funciona correctamente
 *  - CRUD: index, create, store, edit, update, destroy
 *  - Preview devuelve texto con tokens reemplazados
 */
class PublicationTemplateTest extends TestCase
{
    use DatabaseTransactions;

    private TemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TemplateService::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeUser(): User
    {
        $user = User::factory()->create();
        $org  = Organization::create([
            'type' => 'productora',
            'name' => 'Template Org ' . uniqid(),
            'slug' => 'template-org-' . uniqid(),
        ]);
        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id'         => $user->id,
            'role'            => 'owner',
            'status'          => 'active',
        ]);
        return $user;
    }

    private function makeTarget(User $user, string $provider = 'facebook', string $variant = 'facebook_default'): PublicationTarget
    {
        $org = Organization::whereHas('members', fn($q) => $q->where('user_id', $user->id))->first();

        $event = Event::create([
            'organization_id'  => $org->id,
            'title'            => 'Template Event ' . uniqid(),
            'slug'             => 'template-event-' . uniqid(),
            'editorial_status' => 'approved',
            'status'           => 'active',
            'start_at'         => now()->addDays(5),
            'city'             => 'Córdoba',
            'excerpt'          => 'Un evento de prueba increíble.',
        ]);

        $request = PublicationRequest::create([
            'content_type'         => Event::class,
            'content_id'           => $event->id,
            'requested_by'         => $user->id,
            'mode'                 => 'full',
            'wants_portal_publish' => false,
            'wants_portal_social'  => false,
            'wants_own_social'     => true,
            'status'               => 'pending',
        ]);

        return PublicationTarget::create([
            'publication_request_id' => $request->id,
            'provider'               => $provider,
            'destination_type'       => 'feed',
            'template_variant'       => $variant,
            'status'                 => 'pending',
            'priority'               => 5,
        ]);
    }

    // -------------------------------------------------------------------------
    // TemplateService — resolución de templates
    // -------------------------------------------------------------------------

    public function test_service_uses_exact_template_match(): void
    {
        $user   = $this->makeUser();
        $target = $this->makeTarget($user, 'facebook', 'facebook_custom');

        PublicationTemplate::create([
            'content_type'  => Event::class,
            'provider'      => 'facebook',
            'variant_name'  => 'facebook_custom',
            'template_text' => 'Custom: {title} en {city}',
            'is_active'     => true,
        ]);

        $event = Event::find($target->request->content_id);
        $text  = $this->service->render($target, $event);

        $this->assertStringContainsString('Custom:', $text);
        $this->assertStringContainsString($event->title, $text);
        $this->assertStringContainsString('Córdoba', $text);
    }

    public function test_service_falls_back_to_provider_template_without_variant(): void
    {
        $user   = $this->makeUser();
        $target = $this->makeTarget($user, 'facebook', 'facebook_nonexistent');

        // Only provider-level template, no variant match
        PublicationTemplate::create([
            'content_type'  => Event::class,
            'provider'      => 'facebook',
            'variant_name'  => 'any_variant',
            'template_text' => 'Fallback: {title}',
            'is_active'     => true,
        ]);

        $event = Event::find($target->request->content_id);
        $text  = $this->service->render($target, $event);

        $this->assertStringContainsString('Fallback:', $text);
    }

    public function test_service_uses_default_copy_when_no_template(): void
    {
        $user   = $this->makeUser();
        $target = $this->makeTarget($user, 'telegram', 'telegram_default');

        $event = Event::find($target->request->content_id);
        $text  = $this->service->render($target, $event);

        // Default copy = title + excerpt
        $this->assertStringContainsString($event->title, $text);
    }

    public function test_inactive_template_is_not_used(): void
    {
        $user   = $this->makeUser();
        $target = $this->makeTarget($user, 'facebook', 'facebook_default');

        PublicationTemplate::create([
            'content_type'  => Event::class,
            'provider'      => 'facebook',
            'variant_name'  => 'facebook_default',
            'template_text' => 'Inactive template: {title}',
            'is_active'     => false,
        ]);

        $event = Event::find($target->request->content_id);
        $text  = $this->service->render($target, $event);

        // Should not use inactive template
        $this->assertStringNotContainsString('Inactive template:', $text);
    }

    public function test_token_replacement_works_correctly(): void
    {
        $user   = $this->makeUser();
        $target = $this->makeTarget($user, 'instagram', 'instagram_default');

        PublicationTemplate::create([
            'content_type'  => Event::class,
            'provider'      => 'instagram',
            'variant_name'  => 'instagram_default',
            'template_text' => '{title} | {city} | {date} | {excerpt}',
            'is_active'     => true,
        ]);

        $event = Event::find($target->request->content_id);
        $text  = $this->service->render($target, $event);

        $this->assertStringContainsString($event->title, $text);
        $this->assertStringContainsString('Córdoba', $text);
        $this->assertStringContainsString($event->excerpt, $text);
        // Date token is empty since we set it from start_at
        $this->assertStringNotContainsString('{date}', $text);
    }

    // -------------------------------------------------------------------------
    // CRUD — index y create
    // -------------------------------------------------------------------------

    public function test_admin_can_see_templates_index(): void
    {
        $user = $this->makeUser();

        PublicationTemplate::create([
            'provider'      => 'facebook',
            'variant_name'  => 'test_variant',
            'template_text' => 'Hello {title}',
            'is_active'     => true,
        ]);

        $this->actingAs($user)
            ->get(route('pasarela.templates.index'))
            ->assertOk()
            ->assertViewIs('pasarela.templates.index')
            ->assertSee('test_variant');
    }

    public function test_admin_can_create_template(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('pasarela.templates.store'), [
                'provider'      => 'facebook',
                'content_type'  => Event::class,
                'variant_name'  => 'new_variant',
                'template_text' => '{title} - {excerpt}',
                'is_active'     => '1',
            ])
            ->assertRedirect(route('pasarela.templates.index'));

        $this->assertDatabaseHas('publication_templates', [
            'provider'     => 'facebook',
            'variant_name' => 'new_variant',
            'is_active'    => true,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('pasarela.templates.store'), [])
            ->assertSessionHasErrors(['provider', 'variant_name', 'template_text']);
    }

    public function test_admin_can_update_template(): void
    {
        $user = $this->makeUser();

        $template = PublicationTemplate::create([
            'provider'      => 'telegram',
            'variant_name'  => 'v1',
            'template_text' => 'Old text',
            'is_active'     => true,
        ]);

        $this->actingAs($user)
            ->put(route('pasarela.templates.update', $template), [
                'provider'      => 'telegram',
                'variant_name'  => 'v1',
                'template_text' => 'New text: {title}',
                'is_active'     => '1',
            ])
            ->assertRedirect(route('pasarela.templates.index'));

        $this->assertEquals('New text: {title}', $template->fresh()->template_text);
    }

    public function test_admin_can_delete_template(): void
    {
        $user = $this->makeUser();

        $template = PublicationTemplate::create([
            'provider'      => 'instagram',
            'variant_name'  => 'deletable',
            'template_text' => 'Delete me',
            'is_active'     => false,
        ]);

        $this->actingAs($user)
            ->delete(route('pasarela.templates.destroy', $template))
            ->assertRedirect(route('pasarela.templates.index'));

        $this->assertDatabaseMissing('publication_templates', ['id' => $template->id]);
    }

    // -------------------------------------------------------------------------
    // Preview
    // -------------------------------------------------------------------------

    public function test_preview_replaces_tokens_with_sample_data(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->postJson(route('pasarela.templates.preview'), [
                'template_text' => '{title} en {city} el {date}',
            ])
            ->assertOk()
            ->assertJsonPath('preview', fn($v) => str_contains($v, 'Festival de Folklore 2026'))
            ->assertJsonPath('preview', fn($v) => str_contains($v, 'Buenos Aires'));
    }
}
