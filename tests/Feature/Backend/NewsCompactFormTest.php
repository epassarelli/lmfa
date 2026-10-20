<?php

namespace Tests\Feature\Backend;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NewsCompactFormTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_news_form_uses_the_compact_four_column_layout(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $admin->assignRole('administrador');

        $response = $this->actingAs($admin)->get(route('backend.news.create'))->assertOk()
            ->assertSee('class="form-compact"', false)
            // Grilla de 12: fila 1 = 4 + 4 + 2 + 2, fila 2 = 3 + 3 + 3 + 3, fila 3 = cuerpo.
            ->assertSee('col-md-6 col-xl-4', false)
            ->assertSee('col-md-6 col-xl-2', false)
            ->assertSee('col-md-6 col-xl-3', false)
            // Los campos conservan nombre e id; el cuerpo queda al final.
            ->assertSeeInOrder(['id="titulo"', 'id="slug"', 'id="publicar"', 'id="estado"', 'id="categoria_id"', 'id="foto"',
                'id="interprete_principal_id"', 'id="interprete_secundarios"', 'id="editor"'], false)
            // La ayuda de campo es un ícono con tooltip, no texto fijo.
            ->assertSee('data-toggle="tooltip"', false)
            ->assertSee('data-image-hint="news"', false)
            ->assertDontSee('Se completa sola desde el título</small>', false);

        $html = $response->getContent();
        $this->assertStringNotContainsString('col-md-11', $html);

        // La ayuda general arranca contraída (el <details> no lleva "open").
        $this->assertMatchesRegularExpression('/<details class="callout callout-info mb-3" data-form-help="news">/', $html);

        // Obligatorios = lo que exige NoticiaRequest: la fecha es opcional y el estado siempre trae valor.
        $this->assertDoesNotMatchRegularExpression('/<input type="datetime-local" name="publicar"[^>]*\srequired/', $html);
        $this->assertDoesNotMatchRegularExpression('/<select name="estado"[^>]*\srequired/', $html);
        $this->assertMatchesRegularExpression('/<input type="text" name="titulo"[^>]*\srequired/', $html);
        $this->assertMatchesRegularExpression('/<select name="categoria_id"[^>]*\srequired/', $html);
    }
}
