<?php

namespace Tests\Feature\Backend;

use App\Models\Album;
use App\Models\Interprete;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Spec: project/docs/specs/discos-listado-filtro-interprete.md */
class AlbumListFilterTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $user->assignRole('administrador');

        return $user;
    }

    private function albumOf(string $artistName, string $albumName, User $author): Album
    {
        $artist = Interprete::create(['interprete' => $artistName, 'slug' => 'a-'.uniqid(), 'biografia' => '<p>b</p>', 'estado' => 1, 'user_id' => $author->id]);

        return Album::create(['album' => $albumName, 'slug' => 'd-'.uniqid(), 'anio' => '2024', 'interprete_id' => $artist->id, 'user_id' => $author->id, 'estado' => 0, 'visitas' => 0]);
    }

    public function test_filter_by_interprete_lists_only_that_artists_albums_and_keeps_the_selection(): void
    {
        $admin = $this->admin();
        $mine = $this->albumOf('Filtro Uno QA', 'Disco Del Uno QA', $admin);
        $this->albumOf('Filtro Dos QA', 'Disco Del Dos QA', $admin);

        $html = $this->actingAs($admin)->get(route('backend.discos.index', ['interprete_id' => $mine->interprete_id]))
            ->assertOk()->assertSee('Disco Del Uno QA')->assertDontSee('Disco Del Dos QA')->getContent();

        $this->assertMatchesRegularExpression('/<option value="'.$mine->interprete_id.'"\s+selected>\s*Filtro Uno QA/', $html);

        $this->actingAs($admin)->get(route('backend.discos.index', ['search' => 'Del']))
            ->assertOk()->assertSee('Disco Del Uno QA')->assertSee('Disco Del Dos QA');
    }

    public function test_scoped_collaborator_only_gets_artists_of_visible_albums_in_the_selector(): void
    {
        $admin = $this->admin();
        $collab = User::factory()->create();
        Role::findOrCreate('colaborador', 'web');
        $collab->assignRole('colaborador');
        $own = $this->albumOf('Propio Visible QA', 'Disco Propio QA', $collab);
        $this->albumOf('Ajeno Oculto QA', 'Disco Ajeno QA', $admin);

        $this->actingAs($collab)->get(route('backend.discos.index'))
            ->assertOk()->assertSee('Propio Visible QA')->assertDontSee('Ajeno Oculto QA');
    }

    public function test_invalid_interprete_filter_does_not_break_the_list(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('backend.discos.index', ['interprete_id' => 'abc']))->assertOk();
        $this->actingAs($admin)->get(route('backend.discos.index', ['interprete_id' => 99999999]))->assertOk()->assertSee('No hay discos');
    }

    public function test_cover_is_wrapped_in_a_fixed_small_box(): void
    {
        $admin = $this->admin();
        $album = $this->albumOf('Portada Lista QA', 'Disco Portada Lista QA', $admin);
        $album->images()->create([
            'profile' => 'album', 'disk' => 'public', 'original_path' => 'albunes/2026/11/qa_original.jpg',
            'variants_json' => ['card' => [480 => 'albunes/2026/11/qa_card_480.webp']],
            'original_width' => 640, 'original_height' => 640, 'mime' => 'image/jpeg', 'alt' => 'Portada QA',
        ]);

        $html = $this->actingAs($admin)->get(route('backend.discos.index', ['interprete_id' => $album->interprete_id]))->assertOk()->getContent();

        $this->assertStringContainsString('<div class="album-list-thumb">', $html);
        $this->assertStringContainsString('.album-list-thumb { width: 50px; height: 50px;', $html);
    }
}
