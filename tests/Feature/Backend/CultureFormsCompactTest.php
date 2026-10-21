<?php

namespace Tests\Feature\Backend;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\Interprete;
use App\Models\Mito;
use App\Models\PeniaProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Discos, Canciones, Mitos y Peñas: grilla de 12, controles compactos y ayudas en tooltip. */
class CultureFormsCompactTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }

    private function artist(User $author): Interprete
    {
        return Interprete::create(['interprete' => 'Artista QA '.uniqid(), 'slug' => 'a-'.uniqid(), 'biografia' => '<p>b</p>', 'estado' => 1, 'user_id' => $author->id]);
    }

    private function assertCompact(string $html, string $order): void
    {
        $this->assertStringContainsString('class="form-compact"', $html);
        $this->assertMatchesRegularExpression($order, $html);
        $this->assertStringContainsString('data-toggle="tooltip"', $html);
        $this->assertStringContainsString('col-md-', $html);
        $this->assertStringContainsString('col-xl-', $html);
        $this->assertStringNotContainsString('col-md-10 mx-auto', $html);
    }

    public function test_discos_form_uses_the_compact_grid_and_keeps_the_songs_panel(): void
    {
        $admin = $this->user('administrador');
        $album = Album::create(['album' => 'Disco compacto QA', 'slug' => 'd-'.uniqid(), 'anio' => '2024', 'interprete_id' => $this->artist($admin)->id, 'user_id' => $admin->id, 'estado' => 1, 'visitas' => 0]);

        $create = $this->actingAs($admin)->get(route('backend.discos.create'))->assertOk()->getContent();
        $this->assertCompact($create, '/id="album".*id="interprete_id".*id="anio".*id="album_type".*id="release_date".*id="label".*id="spotify".*id="slug".*id="excerpt".*id="foto".*id="seo_title"/s');
        $this->assertStringContainsString('data-image-hint="album"', $create);
        $this->assertStringNotContainsString('id="canciones-list"', $create);
        // Obligatorios (AlbumRequest): nombre, intérprete y año. El slug es opcional.
        $this->assertMatchesRegularExpression('/<input type="text" name="album"[^>]*\srequired/', $create);
        $this->assertDoesNotMatchRegularExpression('/<input type="text" name="slug"[^>]*\srequired/', $create);

        $edit = $this->actingAs($admin)->get(route('backend.discos.edit', $album))->assertOk()->getContent();
        $this->assertCompact($edit, '/id="album".*id="foto".*id="canciones-list".*id="canciones-selector".*id="add-cancion".*id="new-cancion".*id="create-cancion"/s');
        // El JS del panel de canciones necesita estos ids y jQuery cargado antes que Select2/tooltips (van en la sección js).
        $this->assertSame(1, substr_count($edit, 'id="canciones-list"'));
    }

    public function test_canciones_form_uses_the_compact_grid(): void
    {
        $admin = $this->user('administrador');
        $song = Cancion::create(['cancion' => 'Cancion compacta QA', 'slug' => 'c-'.uniqid(), 'letra' => 'x', 'visitas' => 0, 'estado' => 1, 'user_id' => $admin->id, 'interprete_id' => $this->artist($admin)->id]);
        $order = '/id="cancion".*id="interprete_id".*id="slug".*id="publicar".*id="composer".*id="lyricist".*id="rights_status".*id="is_instrumental".*id="lyrics_source_url".*id="youtube".*id="spotify".*id="excerpt".*id="editor".*id="seo_title"/s';

        foreach ([route('backend.canciones.create'), route('backend.canciones.edit', $song)] as $url) {
            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();
            $this->assertCompact($html, $order);
            $this->assertDoesNotMatchRegularExpression('/<input type="text" name="slug"[^>]*\srequired/', $html);
        }

        // Un colaborador no ve slug ni fecha de publicación.
        $html = $this->actingAs($this->user('colaborador'))->get(route('backend.canciones.create'))->assertOk()->getContent();
        $this->assertStringNotContainsString('name="slug"', $html);
        $this->assertStringNotContainsString('name="publicar"', $html);
    }

    public function test_mitos_form_uses_the_compact_grid(): void
    {
        $admin = $this->user('administrador');
        $mito = Mito::create(['titulo' => 'Mito compacto QA', 'slug' => 'm-'.uniqid(), 'mito' => '<p>x</p>', 'estado' => 1, 'user_id' => $admin->id]);
        $order = '/id="titulo".*id="content_type".*id="region".*id="slug".*id="publicar".*id="excerpt".*id="foto".*id="editor".*id="seo_title"/s';

        $create = $this->actingAs($admin)->get(route('backend.mitos.create'))->assertOk()->getContent();
        $this->assertCompact($create, $order);
        $this->assertStringContainsString('data-image-hint="mito"', $create);
        $this->assertStringNotContainsString('name="estado"', $create);

        $edit = $this->actingAs($admin)->get(route('backend.mitos.edit', $mito))->assertOk()->getContent();
        $this->assertCompact($edit, $order);
        $this->assertDoesNotMatchRegularExpression('/<select name="estado"[^>]*\srequired/', $edit);
        $this->assertMatchesRegularExpression('/<input type="text" name="titulo"[^>]*\srequired/', $edit);
        $this->assertMatchesRegularExpression('/<textarea name="mito"[^>]*\srequired/', $edit);
    }

    public function test_penias_form_uses_the_compact_grid_with_labelled_fields(): void
    {
        $admin = $this->user('administrador');
        $profile = PeniaProfile::factory()->create();
        $order = '/id="title".*id="slug".*id="venue_type".*id="capacity".*id="province_id".*id="locality_id".*id="city".*id="address".*id="latitude".*id="longitude".*id="phone".*id="email".*id="website".*id="reservation_url".*id="accessibility_notes".*id="event_ids".*id="excerpt".*id="regular_events_summary".*id="admission_notes".*id="editor".*id="source_urls".*id="seo_title"/s';

        foreach ([route('backend.penia-profiles.create'), route('backend.penia-profiles.edit', $profile)] as $url) {
            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();
            $this->assertCompact($html, $order);
            // Cada etiqueta apunta a su campo, y el equipo editorial ve la gestión de estado.
            $this->assertStringContainsString('<label for="title">', $html);
            $this->assertStringContainsString('id="editorial_status"', $html);
            $this->assertMatchesRegularExpression('/<input id="title"[^>]*\srequired/', $html);
            $this->assertMatchesRegularExpression('/<textarea id="source_urls"[^>]*\srequired/', $html);
        }
    }
}
