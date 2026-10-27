<?php

namespace Tests\Feature\Backend;

use App\Models\Comida;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ComidaCompactFormTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }

    private function assertCommonLayout(string $html): void
    {
        $this->assertStringContainsString('class="form-compact"', $html);

        // Los campos conservan nombre e id y siguen el orden de la grilla de 12.
        $this->assertMatchesRegularExpression('/id="titulo".*id="publicar".*id="region".*id="servings".*id="prep_time_minutes".*id="cook_time_minutes".*id="excerpt".*id="foto".*id="ingredients".*id="instructions".*id="editor".*id="seo_title"/s', $html);

        // Ayudas como tooltip, no como texto fijo bajo el campo.
        $this->assertStringContainsString('data-toggle="tooltip"', $html);
        $this->assertStringContainsString('data-image-hint="recipe"', $html);
        $this->assertStringNotContainsString('Un paso por línea, en orden.</small>', $html);

        // Obligatorios según ComidaRequest: título y receta.
        $this->assertMatchesRegularExpression('/<input type="text" name="titulo"[^>]*\srequired/', $html);
        $this->assertMatchesRegularExpression('/<textarea name="receta"[^>]*\srequired/', $html);

        // La ayuda general arranca contraída.
        $this->assertMatchesRegularExpression('/<details class="callout callout-info mb-3" data-form-help="comida">/', $html);
    }

    public function test_the_administrator_create_form_uses_the_compact_grid(): void
    {
        $html = $this->actingAs($this->user('administrador'))->get(route('backend.comidas.create'))->assertOk()->getContent();

        $this->assertCommonLayout($html);
        // El slug es solo para el administrador y es opcional (el servidor lo genera desde el título).
        $this->assertMatchesRegularExpression('/id="titulo".*id="slug".*id="publicar"/s', $html);
        $this->assertDoesNotMatchRegularExpression('/<input type="text" name="slug"[^>]*\srequired/', $html);
        $this->assertStringNotContainsString('name="estado"', $html);
    }

    public function test_the_administrator_edit_form_offers_the_status_without_marking_it_required(): void
    {
        $admin = $this->user('administrador');
        $comida = Comida::create(['titulo' => 'Locro compacto QA', 'slug' => 'c-'.uniqid(), 'receta' => '<p>x</p>', 'estado' => 1, 'user_id' => $admin->id]);

        $html = $this->actingAs($admin)->get(route('backend.comidas.edit', $comida))->assertOk()->getContent();

        $this->assertCommonLayout($html);
        $this->assertMatchesRegularExpression('/<select name="estado"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<select name="estado"[^>]*\srequired/', $html);
        $this->assertStringContainsString('Locro compacto QA', $html);
    }

    public function test_a_collaborator_does_not_see_slug_or_status(): void
    {
        $html = $this->actingAs($this->user('colaborador'))->get(route('backend.comidas.create'))->assertOk()->getContent();

        $this->assertCommonLayout($html);
        $this->assertStringNotContainsString('name="slug"', $html);
        $this->assertStringNotContainsString('name="estado"', $html);
    }
}
