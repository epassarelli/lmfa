<?php

namespace Tests\Feature\Festivals;

use App\Models\Festival;
use App\Models\Interprete;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FestivalApiModernizationTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeAdminUser(): User
    {
        Role::findOrCreate('administrador', 'web');

        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    private function makeProvince(): int
    {
        return DB::table('provincias')->insertGetId([
            'nombre' => 'Provincia Festival API',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureMonth(int $id = 1, string $name = 'Enero'): void
    {
        DB::table('meses')->updateOrInsert(
            ['id' => $id],
            [
                'nombre' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /** @test */
    public function admin_can_create_a_published_festival_with_seo_media_alt_and_artist_relation(): void
    {
        $this->ensureMonth();
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $provinceId = $this->makeProvince();

        $artist = Interprete::create([
            'interprete' => 'Artista Festival API',
            'slug' => 'artista-festival-api',
            'biografia' => '',
            'estado' => 1,
            'user_id' => $admin->id,
        ]);

        $response = $this->postJson('/api/v1/festivals', [
            'title' => 'Festival API Moderno',
            'slug' => 'festival-api-moderno',
            'excerpt' => 'Bajada editorial del festival.',
            'body' => '<h1>No debe persistir</h1><p>Historia estable del festival.</p>',
            'province_id' => $provinceId,
            'mes_id' => 1,
            'user_id' => $admin->id,
            'status' => 'published',
            'seo_title' => 'Festival API Moderno | Folklore Argentino',
            'meta_description' => 'Descripción SEO del festival para validar el contrato moderno.',
            'image_alt' => 'Escena representativa del Festival API Moderno',
            'interprete_ids' => [$artist->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('slug', 'festival-api-moderno')
            ->assertJsonPath('status', 'published')
            ->assertJsonPath('seo_title', 'Festival API Moderno | Folklore Argentino')
            ->assertJsonPath('image_alt', 'Escena representativa del Festival API Moderno');

        $festival = Festival::where('slug', 'festival-api-moderno')->firstOrFail();

        $this->assertNotNull($festival->published_at);
        $this->assertStringNotContainsString('<h1', strtolower($festival->body));
        $this->assertSame([$artist->id], $festival->interpretes()->pluck('interpretes.id')->all());
    }


    /** @test */
    public function api_defaults_new_festival_to_authenticated_author_and_draft_status(): void
    {
        $this->ensureMonth();

        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $provinceId = $this->makeProvince();

        $response = $this->postJson('/api/v1/festivals', [
            'title' => 'Festival API Borrador',
            'body' => '<p>Contenido editorial.</p>',
            'province_id' => $provinceId,
            'mes_id' => 1,
        ]);

        $response->assertCreated()
            ->assertJsonPath('slug', 'festival-api-borrador')
            ->assertJsonPath('status', 'draft')
            ->assertJsonPath('user_id', $admin->id);

        $this->assertDatabaseHas('festivales', [
            'slug' => 'festival-api-borrador',
            'status' => 'draft',
            'user_id' => $admin->id,
        ]);
    }

    /** @test */
    public function api_rejects_duplicate_festival_slug(): void
    {
        $this->ensureMonth();

        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $provinceId = $this->makeProvince();

        Festival::create([
            'title' => 'Festival Existente',
            'slug' => 'festival-duplicado',
            'body' => '<p>Contenido existente.</p>',
            'province_id' => $provinceId,
            'mes_id' => 1,
            'user_id' => $admin->id,
            'status' => 'draft',
        ]);

        $response = $this->postJson('/api/v1/festivals', [
            'title' => 'Festival Duplicado',
            'slug' => 'festival-duplicado',
            'body' => '<p>Otro contenido.</p>',
            'province_id' => $provinceId,
            'mes_id' => 1,
            'user_id' => $admin->id,
            'status' => 'draft',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function api_update_is_idempotent_and_preserves_status_and_omitted_relations(): void
    {
        $this->ensureMonth();

        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $artist = Interprete::create([
            'interprete' => 'Artista relacionado existente',
            'slug' => 'artista-relacionado-existente',
            'biografia' => '',
            'estado' => 1,
            'user_id' => $admin->id,
        ]);

        $festival = Festival::create([
            'title' => 'Festival para actualizar',
            'slug' => 'festival-para-actualizar',
            'body' => '<p>Contenido editorial existente.</p>',
            'province_id' => $this->makeProvince(),
            'mes_id' => 1,
            'user_id' => $admin->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $festival->interpretes()->attach($artist);

        $payload = ['seo_title' => 'SEO refrescado del festival'];

        $this->putJson("/api/v1/festivals/{$festival->id}", $payload)
            ->assertOk()
            ->assertJsonPath('id', $festival->id)
            ->assertJsonPath('status', 'published')
            ->assertJsonPath('seo_title', 'SEO refrescado del festival');

        $this->putJson("/api/v1/festivals/{$festival->id}", $payload)
            ->assertOk()
            ->assertJsonPath('id', $festival->id)
            ->assertJsonPath('status', 'published');

        $festival->refresh();

        $this->assertSame('published', $festival->status);
        $this->assertNotNull($festival->published_at);
        $this->assertSame([$artist->id], $festival->interpretes()->pluck('interpretes.id')->all());
    }
}
