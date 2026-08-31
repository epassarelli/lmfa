<?php

namespace Tests\Feature\Festivals;

use App\Models\Locality;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EditorialCatalogApiTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function authenticated_client_can_resolve_provinces_localities_and_months(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $province = Provincia::create(['nombre' => 'Provincia Catalogo']);
        $locality = Locality::create([
            'province_id' => $province->id,
            'name' => 'Localidad Catalogo',
        ]);

        DB::table('meses')->insert([
            'id' => 2,
            'nombre' => 'Febrero',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/v1/editorial-catalogs/provinces')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $province->id,
                'name' => 'Provincia Catalogo',
                'slug' => 'provincia-catalogo',
            ]);

        $this->getJson('/api/v1/editorial-catalogs/localities?province_id='.$province->id)
            ->assertOk()
            ->assertJsonFragment([
                'id' => $locality->id,
                'province_id' => $province->id,
                'name' => 'Localidad Catalogo',
            ]);

        $this->getJson('/api/v1/editorial-catalogs/months')
            ->assertOk()
            ->assertJsonFragment([
                'id' => 2,
                'name' => 'Febrero',
            ]);
    }

    /** @test */
    public function editorial_catalogs_require_a_valid_sanctum_token(): void
    {
        $this->getJson('/api/v1/editorial-catalogs/provinces')->assertUnauthorized();
        $this->getJson('/api/v1/editorial-catalogs/localities')->assertUnauthorized();
        $this->getJson('/api/v1/editorial-catalogs/months')->assertUnauthorized();
    }
}
