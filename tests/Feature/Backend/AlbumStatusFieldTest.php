<?php

namespace Tests\Feature\Backend;

use App\Models\Album;
use App\Models\Interprete;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Discos: selector de estado en crear/editar y miniatura de portada dentro del cuadro del upload. */
class AlbumStatusFieldTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $user->assignRole('administrador');

        return $user;
    }

    private function album(User $author, int $estado): Album
    {
        $artist = Interprete::create(['interprete' => 'Artista QA '.uniqid(), 'slug' => 'a-'.uniqid(), 'biografia' => '<p>b</p>', 'estado' => 1, 'user_id' => $author->id]);

        return Album::create(['album' => 'Disco estado QA', 'slug' => 'd-'.uniqid(), 'anio' => '2024', 'interprete_id' => $artist->id, 'user_id' => $author->id, 'estado' => $estado, 'visitas' => 0]);
    }

    private function payload(Album $album, array $extra = []): array
    {
        return array_merge(['album' => $album->album, 'anio' => '2024', 'interprete_id' => $album->interprete_id], $extra);
    }

    public function test_publisher_sees_status_select_with_current_value_on_edit_and_active_by_default_on_create(): void
    {
        $admin = $this->admin();
        $inactive = $this->album($admin, 0);

        $edit = $this->actingAs($admin)->get(route('backend.discos.edit', $inactive))->assertOk()->getContent();
        $this->assertStringContainsString('name="estado"', $edit);
        $this->assertMatchesRegularExpression('/<option value="0"\s+selected>\s*Inactivo/', $edit);

        $create = $this->actingAs($admin)->get(route('backend.discos.create'))->assertOk()->getContent();
        $this->assertStringContainsString('name="estado"', $create);
        $this->assertMatchesRegularExpression('/<option value="1"\s+selected>\s*Activo/', $create);
    }

    public function test_publisher_can_activate_and_deactivate_from_the_edit_form(): void
    {
        $admin = $this->admin();
        $album = $this->album($admin, 0);

        $this->actingAs($admin)->put(route('backend.discos.update', $album), $this->payload($album, ['estado' => '1']))->assertSessionHasNoErrors();
        $this->assertSame(1, (int) $album->fresh()->estado);

        $this->actingAs($admin)->put(route('backend.discos.update', $album), $this->payload($album, ['estado' => '0']))->assertSessionHasNoErrors();
        $this->assertSame(0, (int) $album->fresh()->estado);
    }

    public function test_saving_an_inactive_album_keeps_it_inactive_when_the_form_says_so(): void
    {
        $admin = $this->admin();
        $album = $this->album($admin, 0);

        $this->actingAs($admin)->put(route('backend.discos.update', $album), $this->payload($album, ['album' => 'Renombrado QA', 'estado' => '0']))->assertSessionHasNoErrors();

        $this->assertSame(0, (int) $album->fresh()->estado);
        $this->assertSame('Renombrado QA', $album->fresh()->album);
    }

    public function test_publisher_without_status_in_request_keeps_the_previous_behavior_active(): void
    {
        $admin = $this->admin();
        $album = $this->album($admin, 0);

        $this->actingAs($admin)->put(route('backend.discos.update', $album), $this->payload($album))->assertSessionHasNoErrors();

        $this->assertSame(1, (int) $album->fresh()->estado);
    }

    public function test_status_value_is_validated(): void
    {
        $admin = $this->admin();
        $album = $this->album($admin, 1);

        $this->actingAs($admin)->put(route('backend.discos.update', $album), $this->payload($album, ['estado' => 'quizas']))->assertSessionHasErrors('estado');
    }

    public function test_cover_thumbnail_is_wrapped_in_a_fixed_small_box(): void
    {
        $admin = $this->admin();
        $album = $this->album($admin, 1);
        $album->images()->create([
            'profile' => 'album',
            'disk' => 'public',
            'original_path' => 'albunes/2026/11/qa_original.jpg',
            'variants_json' => ['main' => [400 => 'albunes/2026/11/qa_main_400.webp', 600 => 'albunes/2026/11/qa_main_600.webp']],
            'original_width' => 640,
            'original_height' => 640,
            'mime' => 'image/jpeg',
            'alt' => 'Portada QA',
        ]);

        $edit = $this->actingAs($admin)->get(route('backend.discos.edit', $album))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/class="album-cover-thumb[^"]*"\s+style="width: 56px;"/', $edit);
        $this->assertStringNotContainsString('height: 36px', $edit);
        $this->assertStringContainsString('img-thumbnail img-fluid', $edit);
    }
}
