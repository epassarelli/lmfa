<?php

namespace Tests\Feature\Backend;

use App\Models\Interprete;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InterpreteCompactFormTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $user->assignRole('administrador');

        return $user;
    }

    private function assertCompactLayout(string $html): void
    {
        $this->assertStringContainsString('class="form-compact"', $html);

        // Los campos conservan nombre e id y siguen el orden de la grilla de 12.
        $this->assertMatchesRegularExpression('/id="interprete".*id="slug".*id="artist_type".*id="foto".*id="excerpt".*id="correo".*id="telefono".*id="facebook".*id="instagram".*id="twitter".*id="youtube".*id="web".*id="editor".*id="seo_title"/s', $html);
        $this->assertStringContainsString('col-md-6 col-xl-4', $html);
        $this->assertStringContainsString('col-md-6 col-xl-3', $html);
        $this->assertStringContainsString('col-md-6 col-xl-2', $html);

        // Ayudas como tooltip, no como texto fijo bajo el campo.
        $this->assertStringContainsString('data-toggle="tooltip"', $html);
        $this->assertStringContainsString('data-image-hint="artist"', $html);
        $this->assertStringNotContainsString('Si queda vacío se usa el nombre del artista.</small>', $html);

        // Obligatorios según InterpreteRequest: nombre y biografía. El slug y el estado son opcionales.
        $this->assertMatchesRegularExpression('/<input type="text" name="interprete"[^>]*\srequired/', $html);
        $this->assertMatchesRegularExpression('/<textarea name="biografia"[^>]*\srequired/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input type="text" name="slug"[^>]*\srequired/', $html);
        $this->assertDoesNotMatchRegularExpression('/<select name="estado"[^>]*\srequired/', $html);

        // La ayuda general arranca contraída.
        $this->assertMatchesRegularExpression('/<details class="callout callout-info mb-3" data-form-help="interprete">/', $html);
    }

    public function test_the_create_form_uses_the_compact_grid(): void
    {
        $html = $this->actingAs($this->admin())->get(route('backend.interpretes.create'))->assertOk()->getContent();

        $this->assertCompactLayout($html);
        $this->assertStringNotContainsString('name="estado"', $html);
    }

    public function test_the_edit_form_uses_the_compact_grid_and_lets_the_administrator_set_the_status(): void
    {
        $admin = $this->admin();
        $artist = Interprete::create(['interprete' => 'Artista compacto QA', 'slug' => 'a-'.uniqid(), 'biografia' => '<p>b</p>', 'estado' => 1, 'user_id' => $admin->id]);

        $html = $this->actingAs($admin)->get(route('backend.interpretes.edit', $artist))->assertOk()->getContent();

        $this->assertCompactLayout($html);
        $this->assertStringContainsString('name="estado"', $html);
        $this->assertStringContainsString('Artista compacto QA', $html);
    }
}
