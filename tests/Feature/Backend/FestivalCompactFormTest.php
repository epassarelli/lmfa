<?php

namespace Tests\Feature\Backend;

use App\Models\Festival;
use App\Models\Mes;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FestivalCompactFormTest extends TestCase
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
        $this->assertMatchesRegularExpression('/id="title".*id="slug".*id="published_at".*id="status".*id="province_id".*id="locality_id".*id="mes_id".*id="news_ids".*id="event_ids".*id="interprete_ids".*id="knowledge_article_ids".*id="excerpt".*id="foto".*id="editor".*id="seo_title"/s', $html);
        $this->assertStringContainsString('col-md-12 col-xl-4', $html);
        $this->assertStringContainsString('col-md-6 col-xl-3', $html);

        // Ayudas como tooltip, no como texto fijo bajo el campo.
        $this->assertStringContainsString('data-toggle="tooltip"', $html);
        $this->assertStringContainsString('data-image-hint="festival"', $html);
        $this->assertStringNotContainsString('Si queda vacío, se usa el título del festival.</small>', $html);

        // Obligatorios según FestivalRequest: título, contenido, provincia y mes. Slug, fecha y estado son opcionales.
        $this->assertMatchesRegularExpression('/<input type="text" name="title"[^>]*\srequired/', $html);
        $this->assertMatchesRegularExpression('/<select name="province_id"[^>]*\srequired/', $html);
        $this->assertMatchesRegularExpression('/<select name="mes_id"[^>]*\srequired/', $html);
        $this->assertMatchesRegularExpression('/<textarea name="body"[^>]*\srequired/', $html);
        $this->assertDoesNotMatchRegularExpression('/<select name="status"[^>]*\srequired/', $html);

        // La ayuda general arranca contraída.
        $this->assertMatchesRegularExpression('/<details class="callout callout-info mb-3" data-form-help="festival">/', $html);
    }

    public function test_the_create_form_uses_the_compact_grid(): void
    {
        $html = $this->actingAs($this->admin())->get(route('backend.festivales.create'))->assertOk()
            ->assertSee('<select name="status"', false)->getContent();

        $this->assertCompactLayout($html);
    }

    public function test_the_edit_form_uses_the_compact_grid(): void
    {
        $admin = $this->admin();
        $festival = Festival::create([
            'title' => 'Festival compacto QA', 'slug' => 'f-'.uniqid(), 'status' => 'draft',
            'mes_id' => (Mes::first() ?: Mes::create(['nombre' => 'Enero']))->id,
        ]);

        $html = $this->actingAs($admin)->get(route('backend.festivales.edit', $festival))->assertOk()->getContent();

        $this->assertCompactLayout($html);
        $this->assertStringContainsString('Festival compacto QA', $html);
    }
}
