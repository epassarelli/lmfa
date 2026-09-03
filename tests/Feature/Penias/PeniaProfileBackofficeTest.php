<?php

namespace Tests\Feature\Penias;

use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PeniaProfileBackofficeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('administrador', 'web');
    }

    public function test_an_administrator_can_create_a_verified_penia_from_the_backoffice(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $province = Provincia::create(['nombre' => 'Provincia backoffice '.uniqid()]);

        $this->actingAs($admin)
            ->post(route('backend.penia-profiles.store'), $this->payload($province->id, $admin->id))
            ->assertRedirect(route('backend.penia-profiles.index'));

        $profile = PeniaProfile::where('slug', 'penia-backoffice-verificada')->firstOrFail();

        $this->assertSame($admin->id, $profile->created_by);
        $this->assertSame(['https://example.test/fuente-uno', 'https://example.test/fuente-dos'], $profile->source_urls);
        $this->assertTrue($profile->isPublished());
    }

    public function test_an_administrator_can_open_the_penia_editor_and_a_regular_user_cannot_create_profiles(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $regularUser = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('backend.penia-profiles.create'))
            ->assertOk()
            ->assertSee('Crear Peña')
            ->assertSee('Eventos relacionados');

        $this->actingAs($regularUser)
            ->get(route('backend.penia-profiles.create'))
            ->assertForbidden();
    }

    public function test_an_administrator_can_filter_the_listing_by_territory_type_and_quality_gaps(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $province = Provincia::create(['nombre' => 'Provincia filtros '.uniqid()]);
        $matching = PeniaProfile::factory()->create([
            'title' => 'Peña con faltantes '.uniqid(),
            'province_id' => $province->id,
            'venue_type' => 'centro_cultural',
            'verification_status' => 'pending',
            'last_verified_at' => null,
            'verified_by_user_id' => null,
            'verification_method' => null,
            'source_urls' => [],
            'phone' => null,
            'email' => null,
            'website' => null,
        ]);
        PeniaProfile::factory()->create(['title' => 'Peña excluida '.uniqid()]);

        $this->actingAs($admin)
            ->get(route('backend.penia-profiles.index', [
                'province_id' => $province->id,
                'venue_type' => 'centro_cultural',
                'quality' => 'missing_verification',
            ]))
            ->assertOk()
            ->assertSee($matching->title)
            ->assertDontSee('Peña excluida');
    }

    public function test_an_administrator_can_publish_and_unpublish_a_verified_profile(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $province = Provincia::create(['nombre' => 'Provincia acciones '.uniqid()]);
        $profile = PeniaProfile::factory()->create([
            'province_id' => $province->id,
            'verification_status' => 'verified',
            'last_verified_at' => now(),
            'verified_by_user_id' => $admin->id,
            'verification_method' => 'official_source',
            'editorial_status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->post(route('backend.penia-profiles.publish', $profile))
            ->assertRedirect();
        $this->assertTrue($profile->fresh()->isPublished());

        $this->actingAs($admin)
            ->post(route('backend.penia-profiles.unpublish', $profile))
            ->assertRedirect();
        $this->assertSame('draft', $profile->fresh()->editorial_status);
    }

    public function test_preview_is_noindex_and_does_not_increment_visits(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $province = Provincia::create(['nombre' => 'Provincia preview '.uniqid()]);
        $profile = PeniaProfile::factory()->create([
            'title' => 'Peña vista previa',
            'province_id' => $province->id,
            'created_by' => $admin->id,
            'visits' => 7,
        ]);

        $this->actingAs($admin)
            ->get(route('backend.penia-profiles.preview', $profile))
            ->assertOk()
            ->assertSee('Vista previa editorial')
            ->assertSee('noindex,nofollow', false);

        $this->assertSame(7, $profile->fresh()->visits);
    }

    public function test_the_publish_action_rejects_a_profile_without_current_verification(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $province = Provincia::create(['nombre' => 'Provincia publicación inválida '.uniqid()]);
        $profile = PeniaProfile::factory()->create([
            'province_id' => $province->id,
            'editorial_status' => 'approved',
            'verification_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('backend.penia-profiles.publish', $profile))
            ->assertRedirect()
            ->assertSessionHasErrors('editorial_status');

        $this->assertSame('approved', $profile->fresh()->editorial_status);
    }

    private function payload(int $provinceId, int $userId): array
    {
        return [
            'title' => 'Peña Backoffice Verificada',
            'slug' => 'penia-backoffice-verificada',
            'body' => '<p>Espacio editorialmente verificado.</p>',
            'province_id' => $provinceId,
            'city' => 'Ciudad de prueba',
            'venue_type' => 'penia',
            'source_urls' => "https://example.test/fuente-uno\nhttps://example.test/fuente-dos",
            'verification_status' => 'verified',
            'last_verified_at' => now()->format('Y-m-d\TH:i'),
            'verified_by_user_id' => $userId,
            'verification_method' => 'official_source',
            'editorial_status' => 'published',
        ];
    }
}
