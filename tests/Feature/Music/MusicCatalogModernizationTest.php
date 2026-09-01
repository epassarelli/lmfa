<?php

namespace Tests\Feature\Music;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\Interprete;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MusicCatalogModernizationTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        Role::findOrCreate('administrador', 'web');

        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    private function artist(User $user): Interprete
    {
        return Interprete::create([
            'interprete' => 'Intérprete Catálogo',
            'slug' => 'interprete-catalogo',
            'biografia' => '<p>Biografía de prueba.</p>',
            'user_id' => $user->id,
            'estado' => 1,
        ]);
    }

    /** @test */
    public function api_can_create_album_and_song_without_inventing_lyrics(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);
        $artist = $this->artist($admin);

        $albumResponse = $this->postJson('/api/v1/albums', [
            'album' => 'Disco Moderno',
            'album_type' => 'studio',
            'anio' => '2026',
            'interprete_id' => $artist->id,
            'excerpt' => 'Resumen editorial del disco.',
            'seo_title' => 'Disco Moderno | Intérprete Catálogo',
            'meta_description' => 'Ficha editorial del Disco Moderno.',
        ]);

        $albumResponse->assertCreated()
            ->assertJsonPath('slug', 'disco-moderno')
            ->assertJsonPath('estado', false)
            ->assertJsonPath('user_id', $admin->id);

        $album = Album::where('slug', 'disco-moderno')->firstOrFail();

        $songResponse = $this->postJson('/api/v1/songs', [
            'cancion' => 'Obra sin letra',
            'interprete_id' => $artist->id,
            'excerpt' => 'Ficha de obra sin reproducción de letra.',
            'composer' => 'Compositor Documentado',
            'rights_status' => 'not_available',
            'album_ids' => [$album->id],
        ]);

        $songResponse->assertCreated()
            ->assertJsonPath('slug', 'obra-sin-letra')
            ->assertJsonPath('estado', false)
            ->assertJsonPath('rights_status', 'not_available');

        $song = Cancion::where('slug', 'obra-sin-letra')->firstOrFail();

        $this->assertNull($song->letra);
        $this->assertSame([$album->id], $song->albunes()->pluck('albunes.id')->all());
    }

    /** @test */
    public function song_slug_is_unique_per_artist(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);
        $artist = $this->artist($admin);

        Cancion::create([
            'cancion' => 'Misma Obra',
            'slug' => 'misma-obra',
            'letra' => null,
            'rights_status' => 'not_available',
            'interprete_id' => $artist->id,
            'user_id' => $admin->id,
            'visitas' => 0,
            'estado' => 0,
        ]);

        $this->postJson('/api/v1/songs', [
            'cancion' => 'Misma Obra',
            'interprete_id' => $artist->id,
        ])->assertStatus(422)->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    public function music_auditor_detects_legacy_lyric_placeholder_without_modifying_it(): void
    {
        $admin = $this->admin();
        $artist = $this->artist($admin);

        $song = Cancion::create([
            'cancion' => 'Canción Legacy',
            'slug' => 'cancion-legacy',
            'letra' => 'No disponible aun',
            'interprete_id' => $artist->id,
            'user_id' => $admin->id,
            'visitas' => 0,
            'estado' => 1,
        ]);

        $before = $song->fresh()->letra;

        $exit = Artisan::call('mfa:music:audit', [
            '--active' => true,
            '--limit' => 10,
        ]);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('Placeholders legacy de letra: 1', Artisan::output());
        $this->assertSame($before, $song->fresh()->letra);
    }
}
