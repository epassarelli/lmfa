<?php

namespace Tests\Feature\EntityManagement;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizationContactTrackingTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        Role::findOrCreate('administrador', 'web');
        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    private function organization(array $attributes = []): Organization
    {
        return Organization::create(array_merge([
            'type' => 'artista',
            'name' => 'Org '.uniqid(),
            'status' => 'active',
        ], $attributes));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'artista',
            'name' => 'Contacto '.uniqid(),
            'status' => 'active',
        ], $overrides);
    }

    private function contactable(array $attributes = []): Organization
    {
        return $this->organization(array_merge([
            'email' => 'ok'.uniqid().'@example.test',
            'contact_consent_at' => now(),
        ], $attributes));
    }

    /** @test */
    public function organization_can_be_saved_with_only_a_social_channel_and_defaults_to_prospect(): void
    {
        $this->actingAs($this->admin());
        $payload = $this->payload(['instagram' => '@solo.instagram', 'contact_notes' => 'Le escribí por DM.']);

        $this->post(route('backend.organizations.store'), $payload)->assertSessionHasNoErrors();

        $organization = Organization::where('name', $payload['name'])->firstOrFail();
        $this->assertSame('prospect', $organization->contact_stage);
        $this->assertSame('@solo.instagram', $organization->instagram);
        $this->assertNull($organization->email);
        $this->assertFalse(Organization::contactable()->whereKey($organization->id)->exists());
    }

    /** @test */
    public function stage_and_social_fields_are_validated(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('backend.organizations.store'), $this->payload(['contact_stage' => 'inventada']))
            ->assertSessionHasErrors('contact_stage');
        $this->post(route('backend.organizations.store'), $this->payload(['instagram' => 'no es válido!!']))
            ->assertSessionHasErrors('instagram');
        $this->post(route('backend.organizations.store'), $this->payload(['instagram' => 'https://evil.example.com/x']))
            ->assertSessionHasErrors('instagram');
        $this->post(route('backend.organizations.store'), $this->payload(['facebook' => 'javascript:alert(1)']))
            ->assertSessionHasErrors('facebook');
        $this->post(route('backend.organizations.store'), $this->payload(['instagram' => 'https://www.instagram.com/artista.demo/', 'facebook' => 'https://www.facebook.com/artista', 'contact_stage' => 'replied']))
            ->assertSessionHasNoErrors();
    }

    /** @test */
    public function social_urls_are_built_from_handles_or_kept_when_they_are_urls(): void
    {
        $organization = new Organization(['instagram' => '@artista.demo', 'facebook' => 'festival.demo']);
        $this->assertSame('https://www.instagram.com/artista.demo', $organization->instagram_url);
        $this->assertSame('https://www.facebook.com/festival.demo', $organization->facebook_url);

        $organization = new Organization(['instagram' => 'https://www.instagram.com/otro/', 'facebook' => '  ']);
        $this->assertSame('https://www.instagram.com/otro/', $organization->instagram_url);
        $this->assertNull($organization->facebook_url);
        $this->assertNull((new Organization())->instagram_url);
    }

    /** @test */
    public function registering_a_contact_moves_prospects_to_contacted_and_keeps_later_stages(): void
    {
        $this->actingAs($this->admin());
        $prospect = $this->organization(['contact_stage' => 'prospect']);
        $replied = $this->organization(['contact_stage' => 'replied']);

        $this->post(route('backend.organizations.contacted', $prospect))->assertRedirect();
        $this->post(route('backend.organizations.contacted', $replied))->assertRedirect();

        $prospect->refresh();
        $replied->refresh();
        $this->assertSame('contacted', $prospect->contact_stage);
        $this->assertNotNull($prospect->last_contacted_at);
        $this->assertSame('replied', $replied->contact_stage);
        $this->assertNotNull($replied->last_contacted_at);
    }

    /** @test */
    public function listing_filters_by_stage_and_shows_social_links(): void
    {
        $this->actingAs($this->admin());
        $tag = 'ct'.substr(uniqid(), -6);
        $this->organization(['name' => "Prospecto $tag", 'contact_stage' => 'prospect', 'instagram' => '@pros.'.$tag]);
        $this->organization(['name' => "Respondio $tag", 'contact_stage' => 'replied']);

        $this->get(route('backend.organizations.index', ['search' => $tag, 'stage' => 'prospect']))
            ->assertOk()
            ->assertSee("Prospecto $tag")
            ->assertSee('https://www.instagram.com/pros.'.$tag, false)
            ->assertDontSee("Respondio $tag");
    }

    /** @test */
    public function export_only_includes_contactable_organizations_with_email(): void
    {
        $this->actingAs($this->admin());
        $tag = 'ex'.substr(uniqid(), -6);

        $this->contactable(['name' => "Incluida $tag", 'instagram' => '@inc.'.$tag]);
        $this->contactable(['name' => "Inactiva $tag", 'status' => 'inactive']);
        $this->contactable(['name' => "Baja $tag", 'unsubscribed_at' => now()]);
        $this->organization(['name' => "SinConsentimiento $tag", 'email' => 'nc'.uniqid().'@example.test']);
        $this->organization(['name' => "SinEmail $tag", 'contact_consent_at' => now(), 'instagram' => '@sin.'.$tag]);

        $response = $this->get(route('backend.organizations.export', ['search' => $tag]));
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));

        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF".'nombre,tipo,email', $csv);
        $this->assertStringContainsString("Incluida $tag", $csv);
        $this->assertStringContainsString('https://www.instagram.com/inc.'.$tag, $csv);
        foreach (['Inactiva', 'Baja', 'SinConsentimiento', 'SinEmail'] as $excluded) {
            $this->assertStringNotContainsString("$excluded $tag", $csv, "$excluded no debe exportarse");
        }
    }

    /** @test */
    public function export_neutralizes_spreadsheet_formulas_and_respects_the_type_filter(): void
    {
        $this->actingAs($this->admin());
        $tag = 'fx'.substr(uniqid(), -6);
        $this->contactable(['name' => "=CMD $tag", 'type' => 'productora']);
        $this->contactable(['name' => "Artista $tag", 'type' => 'artista']);

        $csv = $this->get(route('backend.organizations.export', ['search' => $tag, 'type' => 'productora']))->streamedContent();

        $this->assertStringContainsString("'=CMD $tag", $csv);
        $this->assertStringNotContainsString('"=CMD', $csv);
        $this->assertStringNotContainsString(",=CMD", $csv);
        $this->assertStringNotContainsString("Artista $tag", $csv);
    }

    /** @test */
    public function export_and_register_contact_are_forbidden_for_collaborators(): void
    {
        Role::findOrCreate('colaborador', 'web');
        $collaborator = User::factory()->create();
        $collaborator->assignRole('colaborador');
        $organization = $this->organization();

        $this->actingAs($collaborator);
        $this->get(route('backend.organizations.export'))->assertForbidden();
        $this->post(route('backend.organizations.contacted', $organization))->assertForbidden();
        $this->assertNull($organization->fresh()->last_contacted_at);
    }
}
