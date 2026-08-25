<?php

namespace Tests\Feature\Knowledge;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KnowledgeArticleBodyHeadingTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeAdminUser(): User
    {
        Role::findOrCreate('administrador', 'web');

        $user = User::factory()->create();
        $user->assignRole('administrador');

        return $user;
    }

    /** @test */
    public function backend_edit_form_limits_knowledge_article_headings_to_h2_and_h3(): void
    {
        $admin = $this->makeAdminUser();

        $response = $this->actingAs($admin)->get(route('backend.knowledge-articles.create'));

        $response->assertOk()
            ->assertSee('data-ckeditor-profile="editorial-body"', false)
            ->assertSee("view: 'h2'", false)
            ->assertSee("view: 'h3'", false)
            ->assertDontSee("view: 'h1'", false);
    }

    /** @test */
    public function backend_update_normalizes_h1_body_content_to_h2_without_touching_existing_h2_content(): void
    {
        $admin = $this->makeAdminUser();
        $category = KnowledgeCategory::factory()->create();

        $article = KnowledgeArticle::factory()->create([
            'knowledge_category_id' => $category->id,
            'title' => 'Articulo evergreen',
            'slug' => 'articulo-evergreen',
            'body' => '<h2>Subtitulo original</h2><p>Texto inicial.</p>',
            'author_id' => $admin->id,
        ]);

        $this->assertSame('<h2>Subtitulo original</h2><p>Texto inicial.</p>', $article->fresh()->body);

        $response = $this->actingAs($admin)->put(route('backend.knowledge-articles.update', $article), [
            'knowledge_category_id' => $category->id,
            'title' => 'Articulo evergreen',
            'slug' => 'articulo-evergreen',
            'excerpt' => 'Resumen',
            'body' => '<h1>Encabezado editado</h1><p>Texto editado.</p>',
            'editorial_status' => 'draft',
        ]);

        $response->assertRedirect(route('backend.knowledge-articles.edit', $article));

        $article->refresh();

        $this->assertSame('<h2>Encabezado editado</h2><p>Texto editado.</p>', $article->body);
        $this->assertStringNotContainsString('<h1', strtolower($article->body));
    }

    /** @test */
    public function api_normalizes_h1_body_content_to_h2_when_creating_articles(): void
    {
        $admin = $this->makeAdminUser();
        Sanctum::actingAs($admin);

        $category = KnowledgeCategory::factory()->create(['slug' => 'ritmos']);

        $response = $this->postJson('/api/v1/knowledge-articles', [
            'knowledge_category_id' => $category->id,
            'title' => 'Zamba',
            'slug' => 'zamba',
            'body' => '<h1>Historia breve</h1><p>Contenido.</p>',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.body', '<h2>Historia breve</h2><p>Contenido.</p>');

        $this->assertDatabaseHas('knowledge_articles', [
            'slug' => 'zamba',
            'body' => '<h2>Historia breve</h2><p>Contenido.</p>',
        ]);
    }
}
