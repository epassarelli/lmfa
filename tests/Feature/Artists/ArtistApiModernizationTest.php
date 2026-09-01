<?php

namespace Tests\Feature\Artists;

use App\Models\Interprete;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtistApiModernizationTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        Role::findOrCreate('administrador', 'web');

        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    /** @test */
    public function admin_can_create_inactive_artist_with_modern_editorial_fields(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/artists', [
            'interprete' => 'Artista API Moderno',
            'artist_type' => 'soloist',
            'biografia' => '<h1>No debe persistir</h1><p>Trayectoria documentada del artista.</p>',
            'excerpt' => 'Resumen editorial del artista.',
            'seo_title' => 'Artista API Moderno: biografía',
            'meta_description' => 'Biografía y trayectoria de Artista API Moderno.',
            'image_alt' => 'Retrato de Artista API Moderno',
        ]);

        $response->assertCreated()
            ->assertJsonPath('slug', 'artista-api-moderno')
            ->assertJsonPath('artist_type', 'soloist')
            ->assertJsonPath('estado', false)
            ->assertJsonPath('user_id', $admin->id);

        $artist = Interprete::where('slug', 'artista-api-moderno')->firstOrFail();

        $this->assertStringNotContainsString('<h1', strtolower($artist->biografia));
        $this->assertSame('Artista API Moderno: biografía', $artist->seo_title);
    }

    /** @test */
    public function api_rejects_duplicate_artist_slug(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        Interprete::create([
            'interprete' => 'Artista Existente',
            'slug' => 'artista-duplicado',
            'biografia' => '<p>Contenido.</p>',
            'user_id' => $admin->id,
            'estado' => 0,
        ]);

        $response = $this->postJson('/api/v1/artists', [
            'interprete' => 'Otro Artista',
            'slug' => 'artista-duplicado',
            'biografia' => '<p>Otra biografía.</p>',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function api_uses_authenticated_author_and_rejects_oversized_legacy_urls(): void
    {
        $admin = $this->admin();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/artists', [
            'interprete' => 'Autor Protegido',
            'biografia' => '<p>Biografía editorial.</p>',
            'user_id' => $otherUser->id,
            'youtube' => 'https://www.youtube.com/'.str_repeat('a', 240),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'youtube']);

        $validResponse = $this->postJson('/api/v1/artists', [
            'interprete' => 'Autor Protegido',
            'biografia' => '<p>Biografía editorial.</p>',
        ]);

        $validResponse->assertCreated()->assertJsonPath('user_id', $admin->id);
    }

    /** @test */
    public function admin_can_partially_update_artist_editorial_fields_without_changing_ownership(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $artist = Interprete::create([
            'interprete' => 'Artista antes del refresh',
            'slug' => 'artista-antes-del-refresh',
            'biografia' => '<p>Biografía anterior.</p>',
            'user_id' => $admin->id,
            'estado' => 0,
            'visitas' => 17,
        ]);

        $response = $this->putJson("/api/v1/artists/{$artist->id}", [
            'artist_type' => 'group',
            'biografia' => '<h1>No debe persistir</h1><p>Biografía actualizada por la bandeja editorial.</p>',
            'seo_title' => 'Artista actualizado: biografía',
            'meta_description' => 'Trayectoria actualizada del artista.',
        ]);

        $response->assertOk()
            ->assertJsonPath('id', $artist->id)
            ->assertJsonPath('artist_type', 'group')
            ->assertJsonPath('seo_title', 'Artista actualizado: biografía')
            ->assertJsonPath('user_id', $admin->id);

        $artist->refresh();
        $this->assertStringNotContainsString('<h1', strtolower($artist->biografia));
        $this->assertSame(17, $artist->visitas);
        $this->assertSame($admin->id, $artist->user_id);
    }
}
