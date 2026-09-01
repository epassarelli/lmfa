<?php

namespace Tests\Feature\Artists;

use App\Models\Interprete;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtistBackofficeModernizationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function admin_can_keep_artist_inactive_and_persist_youtube_url(): void
    {
        Role::findOrCreate('administrador', 'web');

        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $this->actingAs($admin);

        $artist = Interprete::create([
            'interprete' => 'Artista en revisión',
            'slug' => 'artista-en-revision',
            'biografia' => '<p>Biografía inicial.</p>',
            'estado' => 1,
            'user_id' => $admin->id,
        ]);

        $response = $this->put(route('backend.interpretes.update', $artist), [
            'interprete' => 'Artista en revisión',
            'slug' => 'artista-en-revision',
            'biografia' => '<p>Biografía actualizada.</p>',
            'youtube' => 'https://www.youtube.com/@artista-en-revision',
            'estado' => 0,
        ]);

        $response->assertRedirect(route('backend.interpretes.index'));

        $artist->refresh();
        $this->assertFalse($artist->estado);
        $this->assertSame('https://www.youtube.com/@artista-en-revision', $artist->youtube);
    }
}
