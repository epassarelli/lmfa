<?php

namespace Tests\Feature\Penias;

use App\Models\PeniaProfile;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PeniaProfileApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('administrador', 'web');
    }

    public function test_an_administrator_can_create_a_verified_penia_profile(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $province = Provincia::create(['nombre' => 'Provincia API Peña '.uniqid()]);
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/penia-profiles', $this->payload($province->id, $admin->id));

        $response->assertCreated()
            ->assertJsonPath('editorial_status', 'published')
            ->assertJsonPath('verified_by_user_id', $admin->id);
        $this->assertDatabaseHas('penia_profiles', ['slug' => 'penia-api-verificada']);
    }

    public function test_the_api_rejects_publication_without_current_verification(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $province = Provincia::create(['nombre' => 'Provincia API vencida '.uniqid()]);
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/penia-profiles', array_merge(
            $this->payload($province->id, $admin->id),
            ['last_verified_at' => now()->subDays(91)->toIso8601String()]
        ));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('editorial_status');
    }

    public function test_a_non_administrator_cannot_write_penia_profiles(): void
    {
        $user = User::factory()->create();
        $province = Provincia::create(['nombre' => 'Provincia API sin permiso '.uniqid()]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/penia-profiles', $this->payload($province->id, $user->id))
            ->assertForbidden();
    }

    private function payload(int $provinceId, int $userId): array
    {
        return [
            'title' => 'Peña API Verificada',
            'slug' => 'penia-api-verificada',
            'body' => '<p>Ficha creada mediante API.</p>',
            'province_id' => $provinceId,
            'city' => 'Ciudad API',
            'venue_type' => 'penia',
            'source_urls' => ['https://example.test/penia-api'],
            'verification_status' => 'verified',
            'last_verified_at' => now()->toIso8601String(),
            'verified_by_user_id' => $userId,
            'verification_method' => 'official_source',
            'editorial_status' => 'published',
        ];
    }
}
